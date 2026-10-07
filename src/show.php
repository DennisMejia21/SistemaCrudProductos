<?php
require_once __DIR__ . '/funciones.php';
// show.php?id=3 aporta el ID, no el producto completo. Buscamos una única fila.
$id = idValido($_GET['id'] ?? null);
$producto = $id === null ? null : obtenerProductoPorId($pdo, $id);
if ($producto === null) {
    noEncontrado(); // No intentamos leer columnas de un registro inexistente.
}
$titulo = $producto['nombre'];
require __DIR__ . '/partials/header.php';
?>
<a class="back-link" href="index.php">← Volver a productos</a>
<div class="page-heading"><div><p class="eyebrow">PRODUCTO #<?= h($producto['id']) ?></p><h1><?= h($producto['nombre']) ?></h1><p class="muted">Información del producto y su marca.</p></div><a class="button" href="edit.php?id=<?= h($id) ?>">Editar producto</a></div>
<section class="panel detail-panel">
    <!-- dl relaciona etiquetas y valores, sin inputs que no se pueden editar. -->
    <dl class="details">
        <div><dt>Marca</dt><dd><?= h($producto['marca']) ?></dd></div>
        <div><dt>Precio</dt><dd class="detail-price"><?= h(precioFormateado($producto['precio'])) ?></dd></div>
        <div><dt>Estado</dt><dd><span class="badge <?= $producto['estado'] === 'activo' ? 'badge-active' : 'badge-inactive' ?>"><?= h(ucfirst($producto['estado'])) ?></span></dd></div>
        <div><dt>Fecha de carga</dt><dd><?= h(date('d/m/Y H:i', strtotime($producto['fecha_carga']))) ?> <small class="muted">UTC</small></dd></div>
        <div><dt>ID de la marca</dt><dd><?= h($producto['marca_id']) ?></dd></div>
        <div><dt>ID del producto</dt><dd><?= h($producto['id']) ?></dd></div>
    </dl>
    <div class="form-actions"><a class="button button-light" href="index.php">Volver al listado</a><a class="danger-link" href="delete.php?id=<?= h($id) ?>">Eliminar producto</a></div>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
