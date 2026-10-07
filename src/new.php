<?php
require_once __DIR__ . '/funciones.php';
// Si una validación falló, recuperamos los campos sin escribir todo otra vez.
$formulario = recuperarFormulario('producto');
$valores = array_merge(['nombre' => '', 'marca_id' => '', 'precio' => '', 'estado' => 'activo'], $formulario['valores']);
$errores = $formulario['errores'];
$marcas = obtenerMarcas($pdo);
$accion = 'crear_producto';
$textoBoton = 'Guardar producto';
$titulo = 'Nuevo producto';
require __DIR__ . '/partials/header.php';
?>
<div class="page-heading"><div><p class="eyebrow">ALTA DE PRODUCTO</p><h1>Nuevo producto</h1><p class="muted">Completá los datos. La fecha de carga se guarda automáticamente.</p></div></div>
<?php require __DIR__ . '/partials/formulario-producto.php'; ?>
<?php require __DIR__ . '/partials/footer.php'; ?>
