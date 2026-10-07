<?php
require_once __DIR__ . '/funciones.php';
// edit.php?id=3: $_GET['id'] contiene '3'. Validamos antes de consultar.
$productoId = idValido($_GET['id'] ?? null);
$producto = $productoId === null ? null : obtenerProductoPorId($pdo, $productoId);
if ($producto === null) {
    noEncontrado();
}
$formulario = recuperarFormulario('producto');
// La base llena el formulario; si hubo errores, el último intento tiene prioridad.
$valores = array_merge($producto, $formulario['valores']);
$errores = $formulario['errores'];
$marcas = obtenerMarcas($pdo);
$accion = 'editar_producto';
$textoBoton = 'Guardar cambios';
$titulo = 'Editar producto';
require __DIR__ . '/partials/header.php';
?>
<div class="page-heading"><div><p class="eyebrow">PRODUCTO #<?= h($productoId) ?></p><h1>Editar producto</h1><p class="muted">La fecha de carga original se conserva.</p></div></div>
<?php require __DIR__ . '/partials/formulario-producto.php'; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
