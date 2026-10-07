<?php
require_once __DIR__ . '/funciones.php';
$id = idValido($_GET['id'] ?? null);
$producto = $id === null ? null : obtenerProductoPorId($pdo, $id);
if ($producto === null) {
    noEncontrado();
}
$titulo = 'Eliminar producto';
require __DIR__ . '/partials/header.php';
?>
<div class="page-heading"><div><p class="eyebrow">CONFIRMACIÓN</p><h1>Eliminar producto</h1><p class="muted">Revisá el registro antes de confirmar.</p></div></div>
<section class="panel form-panel">
    <div class="alert alert-error">Esta acción borra el producto. Si solo querés dejar de usarlo, podés cambiar su estado a inactivo.</div>
    <h2><?= h($producto['nombre']) ?></h2><p><?= h($producto['marca']) ?> · <?= h(precioFormateado($producto['precio'])) ?></p>
    <!-- GET solo muestra la confirmación. El POST con token es el que elimina. -->
    <form action="script.php" method="POST" class="form-actions">
        <input type="hidden" name="csrf" value="<?= h(tokenCsrf()) ?>">
        <input type="hidden" name="accion" value="eliminar_producto">
        <input type="hidden" name="id" value="<?= h($id) ?>">
        <button class="button button-danger" type="submit">Sí, eliminar producto</button>
        <a class="button button-light" href="show.php?id=<?= h($id) ?>">Cancelar</a>
    </form>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
