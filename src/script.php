<?php
require_once __DIR__ . '/funciones.php';

// Todas las escrituras pasan por aquí. Abrir script.php en el navegador no escribe.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Esta acción requiere un formulario enviado por POST.');
}
if (!csrfValido($_POST['csrf'] ?? null)) {
    http_response_code(403);
    exit('Formulario vencido o inválido. Volvé a abrirlo e intentá de nuevo.');
}

$accion = textoEntrada($_POST, 'accion');
$accionesPermitidas = ['crear_producto', 'editar_producto', 'eliminar_producto', 'crear_marca', 'editar_marca', 'eliminar_marca'];
if (!in_array($accion, $accionesPermitidas, true)) {
    http_response_code(400);
    exit('Acción desconocida.');
}

// Un ID oculto también puede manipularse. Verificamos el formato y la existencia.
$id = idValido($_POST['id'] ?? null);
if (in_array($accion, ['editar_producto', 'eliminar_producto'], true)) {
    if ($id === null || obtenerProductoPorId($pdo, $id) === null) {
        noEncontrado();
    }
}
if (in_array($accion, ['editar_marca', 'eliminar_marca'], true)) {
    if ($id === null || obtenerMarcaPorId($pdo, $id) === null) {
        noEncontrado('La marca solicitada no existe.');
    }
}

if ($accion === 'crear_producto' || $accion === 'editar_producto') {
    [$valores, $errores] = validarProducto($pdo, $_POST);
    $volver = $accion === 'crear_producto' ? 'new.php' : 'edit.php?id=' . $id;
    if ($errores) {
        guardarFormulario('producto', $valores, $errores);
        redirigir($volver);
    }
    try {
        if ($accion === 'crear_producto') {
            $id = crearProducto($pdo, $valores);
            mensaje('Producto creado correctamente.');
        } else {
            editarProducto($pdo, $id, $valores);
            mensaje('Cambios guardados correctamente.');
        }
    } catch (PDOException $error) {
        // Si otro usuario borró la marca durante el envío, la FK todavía nos protege.
        if ((int) ($error->errorInfo[1] ?? 0) !== 1452) {
            throw $error;
        }
        guardarFormulario('producto', $valores, ['marca_id' => 'La marca ya no existe. Seleccioná otra.']);
        redirigir($volver);
    }
    redirigir('show.php?id=' . $id);
}

if ($accion === 'eliminar_producto') {
    eliminarProducto($pdo, $id);
    mensaje('Producto eliminado correctamente.');
    redirigir('index.php');
}

// Marca solo tiene un campo editable, pero también validamos longitud y unicidad.
if ($accion === 'crear_marca' || $accion === 'editar_marca') {
    $nombre = textoEntrada($_POST, 'marca');
    $volver = $accion === 'crear_marca' ? 'marcas.php' : 'marcas.php?editar=' . $id;
    if (!preg_match('/\A.{1,80}\z/us', $nombre)) {
        guardarFormulario('marca', ['marca' => $nombre], ['marca' => 'Escribí entre 1 y 80 caracteres.']);
        redirigir($volver);
    }
    try {
        if ($accion === 'crear_marca') {
            crearMarca($pdo, $nombre);
            mensaje('Marca creada correctamente.');
        } else {
            editarMarca($pdo, $id, $nombre);
            mensaje('Marca actualizada correctamente.');
        }
    } catch (PDOException $error) {
        // 1062 = clave única duplicada; no dependemos solo de una consulta previa.
        if ((int) ($error->errorInfo[1] ?? 0) !== 1062) {
            throw $error;
        }
        guardarFormulario('marca', ['marca' => $nombre], ['marca' => 'Ya existe una marca con ese nombre.']);
        redirigir($volver);
    }
    redirigir('marcas.php');
}

if ($accion === 'eliminar_marca') {
    try {
        eliminarMarca($pdo, $id);
        mensaje('Marca eliminada correctamente.');
    } catch (PDOException $error) {
        // 1451 = una clave foránea todavía apunta a esta marca.
        if ((int) ($error->errorInfo[1] ?? 0) !== 1451) {
            throw $error;
        }
        mensaje('No se puede eliminar la marca: tiene productos asociados.', 'error');
    }
    redirigir('marcas.php');
}
