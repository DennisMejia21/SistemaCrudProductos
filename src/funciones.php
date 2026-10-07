<?php
// Sesiones: guardan mensajes, errores y el token de formularios entre peticiones.
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

// Protección sencilla sin JavaScript externo: el navegador no ejecuta contenido enviado.
header('X-Content-Type-Options: nosniff');
header('Content-Security-Policy: default-src \'self\'; style-src \'self\'; form-action \'self\'; frame-ancestors \'none\'; base-uri \'self\'');

// No mostramos SQL, claves ni detalles internos ante errores inesperados.
set_exception_handler(function (Throwable $error) {
    error_log('Error de aplicación: ' . get_class($error) . ', código ' . $error->getCode());
    http_response_code(500);
    $titulo = 'No pudimos completar la operación';
    require __DIR__ . '/partials/header.php';
    echo '<section class="panel"><h2>Ocurrió un error</h2><p>Revisá que MariaDB esté funcionando y que las tablas estén preparadas. Consultá los logs de Docker para diagnosticarlo.</p><a class="button" href="index.php">Volver al listado</a></section>';
    require __DIR__ . '/partials/footer.php';
});

require_once __DIR__ . '/conexion.php';

// h() convierte texto a HTML seguro. Por ejemplo, <script> se muestra como texto.
function h($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Rechazamos arrays enviados donde esperábamos un campo de texto.
function textoEntrada(array $datos, string $campo): string
{
    return isset($datos[$campo]) && is_string($datos[$campo]) ? trim($datos[$campo]) : '';
}

function idValido($valor): ?int
{
    if (!is_string($valor) && !is_int($valor)) {
        return null;
    }
    $id = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 4294967295]]);
    return $id === false ? null : $id;
}

// Redirigimos después de POST: actualizar la página no repite INSERT/UPDATE/DELETE.
function redirigir(string $ruta): void
{
    header('Location: ' . $ruta, true, 303);
    exit;
}

function mensaje(string $texto, string $tipo = 'success'): void
{
    $_SESSION['mensaje'] = ['texto' => $texto, 'tipo' => $tipo];
}

// Token aleatorio ligado a la sesión: otro sitio no puede adivinarlo y enviar un POST.
function tokenCsrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfValido($token): bool
{
    return is_string($token) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function guardarFormulario(string $tipo, array $valores, array $errores): void
{
    $_SESSION['formulario_' . $tipo] = ['valores' => $valores, 'errores' => $errores];
}

function recuperarFormulario(string $tipo): array
{
    $datos = $_SESSION['formulario_' . $tipo] ?? ['valores' => [], 'errores' => []];
    unset($_SESSION['formulario_' . $tipo]); // Se muestran una sola vez.
    return $datos;
}

function noEncontrado(string $texto = 'El registro solicitado no existe.'): void
{
    http_response_code(404);
    $titulo = 'Registro no encontrado';
    require __DIR__ . '/partials/header.php';
    echo '<section class="panel"><h2>Registro no encontrado</h2><p>' . h($texto) . '</p><a class="button" href="index.php">Volver</a></section>';
    require __DIR__ . '/partials/footer.php';
    exit;
}

// INNER JOIN reemplaza el ID de marca por su nombre al leer los productos.
function obtenerProductos(PDO $pdo, string $busqueda = '', string $estado = '', ?int $marcaId = null): array
{
    $sql = 'SELECT p.*, m.marca FROM productos p INNER JOIN marcas m ON p.marca_id = m.id WHERE 1 = 1';
    $valores = [];
    if ($busqueda !== '') {
        $sql .= ' AND (p.nombre LIKE ? OR m.marca LIKE ?)';
        $valores[] = '%' . $busqueda . '%';
        $valores[] = '%' . $busqueda . '%';
    }
    if (in_array($estado, ['activo', 'inactivo'], true)) {
        $sql .= ' AND p.estado = ?';
        $valores[] = $estado;
    }
    if ($marcaId !== null) {
        $sql .= ' AND p.marca_id = ?';
        $valores[] = $marcaId;
    }
    $sql .= ' ORDER BY p.id DESC';
    $consulta = $pdo->prepare($sql);
    $consulta->execute($valores); // Los datos nunca se concatenan como SQL.
    return $consulta->fetchAll();
}

function obtenerProductoPorId(PDO $pdo, int $id): ?array
{
    $consulta = $pdo->prepare('SELECT p.*, m.marca FROM productos p INNER JOIN marcas m ON p.marca_id = m.id WHERE p.id = ?');
    $consulta->execute([$id]);
    return $consulta->fetch() ?: null; // fetch obtiene una fila; false significa ausente.
}

function obtenerMarcas(PDO $pdo): array
{
    // LEFT JOIN incluye marcas que todavía no tienen productos. COUNT ignora los NULL.
    return $pdo->query('SELECT m.id, m.marca, COUNT(p.id) AS cantidad FROM marcas m LEFT JOIN productos p ON p.marca_id = m.id GROUP BY m.id, m.marca ORDER BY m.marca')->fetchAll();
}

function obtenerMarcaPorId(PDO $pdo, int $id): ?array
{
    $consulta = $pdo->prepare('SELECT id, marca FROM marcas WHERE id = ?');
    $consulta->execute([$id]);
    return $consulta->fetch() ?: null;
}

function obtenerResumen(PDO $pdo): array
{
    return $pdo->query("SELECT COUNT(*) AS total, COALESCE(SUM(estado = 'activo'), 0) AS activos, COALESCE(SUM(estado = 'inactivo'), 0) AS inactivos FROM productos")->fetch();
}

// Revisamos en servidor aunque HTML también tenga required, min y maxlength.
function validarProducto(PDO $pdo, array $datos): array
{
    $valores = [
        'nombre' => textoEntrada($datos, 'nombre'),
        'marca_id' => textoEntrada($datos, 'marca_id'),
        'precio' => str_replace(',', '.', textoEntrada($datos, 'precio')),
        'estado' => textoEntrada($datos, 'estado'),
    ];
    $errores = [];
    // /u cuenta caracteres UTF-8; /s permite saltos de línea sin romper la validación.
    if (!preg_match('/\A.{1,150}\z/us', $valores['nombre'])) {
        $errores['nombre'] = 'Escribí un nombre de entre 1 y 150 caracteres.';
    }
    $marcaId = idValido($valores['marca_id']);
    if ($marcaId === null || obtenerMarcaPorId($pdo, $marcaId) === null) {
        $errores['marca_id'] = 'Seleccioná una marca que exista.';
    }
    // Hasta 10 dígitos enteros y 2 decimales: coincide con DECIMAL(12,2).
    if (!preg_match('/\A[0-9]{1,10}(?:\.[0-9]{1,2})?\z/', $valores['precio'])) {
        $errores['precio'] = 'Ingresá un precio entre 0 y 9999999999.99, con hasta 2 decimales.';
    }
    if (!in_array($valores['estado'], ['activo', 'inactivo'], true)) {
        $errores['estado'] = 'Seleccioná activo o inactivo.';
    }
    return [$valores, $errores];
}

function crearProducto(PDO $pdo, array $datos): int
{
    $consulta = $pdo->prepare('INSERT INTO productos (nombre, marca_id, precio, estado) VALUES (?, ?, ?, ?)');
    $consulta->execute([$datos['nombre'], $datos['marca_id'], $datos['precio'], $datos['estado']]);
    return (int) $pdo->lastInsertId(); // El ID generado por AUTO_INCREMENT.
}

function editarProducto(PDO $pdo, int $id, array $datos): void
{
    $consulta = $pdo->prepare('UPDATE productos SET nombre = ?, marca_id = ?, precio = ?, estado = ? WHERE id = ?');
    $consulta->execute([$datos['nombre'], $datos['marca_id'], $datos['precio'], $datos['estado'], $id]);
}

function eliminarProducto(PDO $pdo, int $id): void
{
    $consulta = $pdo->prepare('DELETE FROM productos WHERE id = ?');
    $consulta->execute([$id]); // WHERE evita borrar todos los productos.
}

function crearMarca(PDO $pdo, string $nombre): void
{
    $consulta = $pdo->prepare('INSERT INTO marcas (marca) VALUES (?)');
    $consulta->execute([$nombre]);
}

function editarMarca(PDO $pdo, int $id, string $nombre): void
{
    $consulta = $pdo->prepare('UPDATE marcas SET marca = ? WHERE id = ?');
    $consulta->execute([$nombre, $id]);
}

function eliminarMarca(PDO $pdo, int $id): void
{
    $consulta = $pdo->prepare('DELETE FROM marcas WHERE id = ?');
    $consulta->execute([$id]); // La FK rechaza marcas con productos asociados.
}

function precioFormateado($precio): string
{
    return '$ ' . number_format((float) $precio, 2, ',', '.'); // Solo formato visual.
}
