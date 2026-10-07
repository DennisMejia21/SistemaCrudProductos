<?php
require_once __DIR__ . '/funciones.php';
// GET permite compartir la búsqueda en una URL y no cambia la base de datos.
$busqueda = textoEntrada($_GET, 'q');
$estado = textoEntrada($_GET, 'estado');
$marcaId = idValido($_GET['marca_id'] ?? '');
$productos = obtenerProductos($pdo, $busqueda, $estado, $marcaId);
$marcas = obtenerMarcas($pdo);
$resumen = obtenerResumen($pdo);
$titulo = 'Productos';
require __DIR__ . '/partials/header.php';
?>
<div class="page-heading">
    <div><p class="eyebrow">TU CATÁLOGO</p><h1>Productos</h1><p class="muted">Organizá tus productos y mantené sus datos al día.</p></div>
    <a class="button" href="new.php">+ Nuevo producto</a>
</div>
<!-- El resumen cuenta toda la base; la tabla muestra los resultados de los filtros. -->
<section class="stats" aria-label="Resumen del catálogo">
    <div class="stat"><span>Total de productos</span><strong><?= h($resumen['total']) ?></strong></div>
    <div class="stat"><span>Activos</span><strong><?= h($resumen['activos']) ?></strong></div>
    <div class="stat"><span>Inactivos</span><strong><?= h($resumen['inactivos']) ?></strong></div>
    <div class="stat"><span>Marcas</span><strong><?= count($marcas) ?></strong></div>
</section>
<section class="panel">
    <form action="index.php" method="GET" class="filters">
        <div class="field search"><label for="q">Buscar producto o marca</label><input id="q" name="q" value="<?= h($busqueda) ?>" placeholder="Nombre o marca…"></div>
        <div class="field"><label for="marca_id">Marca</label><select id="marca_id" name="marca_id"><option value="">Todas las marcas</option>
            <?php foreach ($marcas as $marca): ?>
                <option value="<?= h($marca['id']) ?>" <?= $marcaId === (int) $marca['id'] ? 'selected' : '' ?>><?= h($marca['marca']) ?></option>
            <?php endforeach; ?>
        </select></div>
        <div class="field"><label for="estado">Estado</label><select id="estado" name="estado"><option value="">Todos</option><option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>Activo</option><option value="inactivo" <?= $estado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option></select></div>
        <button class="button" type="submit">Filtrar</button><a class="text-link" href="index.php">Limpiar</a>
    </form>
    <div class="section-heading"><h2>Listado</h2><span class="muted"><?= count($productos) ?> resultado(s)</span></div>
    <?php if (!$productos): ?>
        <div class="empty"><h3>No hay productos para mostrar</h3><p>Probá cambiar los filtros o cargá tu primer producto.</p><a class="button button-light" href="new.php">Crear producto</a></div>
    <?php else: ?>
        <div class="table-scroll"><table>
            <thead><tr><th scope="col">#</th><th scope="col">Nombre</th><th scope="col">Marca</th><th scope="col">Precio</th><th scope="col">Estado</th><th scope="col">Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($productos as $producto): ?>
                <!-- Cada fila es un array asociativo; ['nombre'] lee esa columna. -->
                <tr>
                    <td class="muted"><?= h($producto['id']) ?></td>
                    <td><a class="product-name" href="show.php?id=<?= h($producto['id']) ?>"><?= h($producto['nombre']) ?></a></td>
                    <td><?= h($producto['marca']) ?></td>
                    <td class="price"><?= h(precioFormateado($producto['precio'])) ?></td>
                    <td><span class="badge <?= $producto['estado'] === 'activo' ? 'badge-active' : 'badge-inactive' ?>"><?= h(ucfirst($producto['estado'])) ?></span></td>
                    <td><div class="actions"><a href="show.php?id=<?= h($producto['id']) ?>">Ver</a><a href="edit.php?id=<?= h($producto['id']) ?>">Editar</a><a class="danger-link" href="delete.php?id=<?= h($producto['id']) ?>">Eliminar</a></div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
