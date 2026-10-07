<?php
require_once __DIR__ . '/funciones.php';
$marcas = obtenerMarcas($pdo);
$marcaEditar = null;
$marcaEliminar = null;

// Las opciones de GET solo eligen qué formulario mostrar; nunca modifican datos.
if (isset($_GET['editar'])) {
    $id = idValido($_GET['editar']);
    $marcaEditar = $id === null ? null : obtenerMarcaPorId($pdo, $id);
    if ($marcaEditar === null) {
        noEncontrado('La marca solicitada no existe.');
    }
}
if (isset($_GET['eliminar'])) {
    $id = idValido($_GET['eliminar']);
    $marcaEliminar = $id === null ? null : obtenerMarcaPorId($pdo, $id);
    if ($marcaEliminar === null) {
        noEncontrado('La marca solicitada no existe.');
    }
}
$formulario = recuperarFormulario('marca');
$nombre = $formulario['valores']['marca'] ?? ($marcaEditar['marca'] ?? '');
$errorMarca = $formulario['errores']['marca'] ?? '';
$titulo = 'Marcas';
$seccion = 'marcas';
require __DIR__ . '/partials/header.php';
?>
<div class="page-heading"><div><p class="eyebrow">ORGANIZACIÓN DEL CATÁLOGO</p><h1>Marcas</h1><p class="muted">Cada producto pertenece a una marca. Crealas una vez y reutilizalas.</p></div></div>
<?php if ($marcaEliminar): ?>
    <section class="panel confirmation">
        <h2>¿Eliminar <?= h($marcaEliminar['marca']) ?>?</h2>
        <p>Si tiene productos asociados, la base impedirá borrarla. Podés editar esos productos para asignarles otra marca.</p>
        <form action="script.php" method="POST" class="form-actions">
            <input type="hidden" name="csrf" value="<?= h(tokenCsrf()) ?>">
            <input type="hidden" name="accion" value="eliminar_marca">
            <input type="hidden" name="id" value="<?= h($marcaEliminar['id']) ?>">
            <button class="button button-danger" type="submit">Sí, eliminar marca</button><a class="button button-light" href="marcas.php">Cancelar</a>
        </form>
    </section>
<?php endif; ?>
<div class="brands-grid">
    <section class="panel">
        <h2><?= $marcaEditar ? 'Editar marca' : 'Nueva marca' ?></h2>
        <form action="script.php" method="POST">
            <input type="hidden" name="csrf" value="<?= h(tokenCsrf()) ?>">
            <input type="hidden" name="accion" value="<?= $marcaEditar ? 'editar_marca' : 'crear_marca' ?>">
            <?php if ($marcaEditar): ?><input type="hidden" name="id" value="<?= h($marcaEditar['id']) ?>"><?php endif; ?>
            <div class="field"><label for="marca">Nombre de la marca</label><input id="marca" name="marca" maxlength="80" required value="<?= h($nombre) ?>" placeholder="Ej. Sony" aria-describedby="error-marca"><small id="error-marca" class="field-error"><?= h($errorMarca) ?></small></div>
            <div class="form-actions"><button class="button" type="submit"><?= $marcaEditar ? 'Guardar cambios' : 'Crear marca' ?></button><?php if ($marcaEditar): ?><a class="text-link" href="marcas.php">Cancelar</a><?php endif; ?></div>
        </form>
    </section>
    <section class="panel">
        <div class="section-heading"><h2>Marcas registradas</h2><span class="muted"><?= count($marcas) ?> marca(s)</span></div>
        <?php if (!$marcas): ?><div class="empty"><h3>Todavía no hay marcas</h3><p>Creá la primera usando el formulario.</p></div><?php else: ?>
        <div class="table-scroll"><table><thead><tr><th scope="col">Marca</th><th scope="col">Productos</th><th scope="col">Acciones</th></tr></thead><tbody>
        <?php foreach ($marcas as $marca): ?>
            <tr><td class="product-name"><?= h($marca['marca']) ?></td><td><a href="index.php?marca_id=<?= h($marca['id']) ?>"><?= h($marca['cantidad']) ?></a></td><td><div class="actions"><a href="marcas.php?editar=<?= h($marca['id']) ?>">Editar</a><a class="danger-link" href="marcas.php?eliminar=<?= h($marca['id']) ?>">Eliminar</a></div></td></tr>
        <?php endforeach; ?>
        </tbody></table></div><?php endif; ?>
    </section>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
