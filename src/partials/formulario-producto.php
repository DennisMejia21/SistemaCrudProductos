<?php // El alta y la edición comparten campos; cambian acción, ID y valores iniciales. ?>
<?php if ($errores): ?>
    <div class="alert alert-error" role="alert">Revisá los campos señalados. Tus datos se conservaron.</div>
<?php endif; ?>
<?php if (!$marcas): ?>
    <div class="alert alert-error">Primero necesitás <a href="marcas.php">crear una marca</a>.</div>
<?php endif; ?>
<form action="script.php" method="POST" class="panel form-panel">
    <!-- Los hidden viajan por POST, pero siguen siendo datos manipulables: se validan. -->
    <input type="hidden" name="csrf" value="<?= h(tokenCsrf()) ?>">
    <input type="hidden" name="accion" value="<?= h($accion) ?>">
    <?php if (isset($productoId)): ?>
        <input type="hidden" name="id" value="<?= h($productoId) ?>">
    <?php endif; ?>

    <div class="field">
        <label for="nombre">Nombre del producto</label>
        <input id="nombre" name="nombre" value="<?= h($valores['nombre']) ?>" maxlength="150" required placeholder="Ej. Auriculares WH-1000XM5" aria-describedby="error-nombre">
        <small id="error-nombre" class="field-error"><?= h($errores['nombre'] ?? '') ?></small>
    </div>
    <div class="form-grid">
        <div class="field">
            <label for="marca_id">Marca</label>
            <select id="marca_id" name="marca_id" required aria-describedby="error-marca">
                <option value="">Seleccioná una marca</option>
                <?php foreach ($marcas as $marca): ?>
                    <!-- Se envía el ID; el estudiante ve el nombre leído de MariaDB. -->
                    <option value="<?= h($marca['id']) ?>" <?= (string) $valores['marca_id'] === (string) $marca['id'] ? 'selected' : '' ?>><?= h($marca['marca']) ?></option>
                <?php endforeach; ?>
            </select>
            <small id="error-marca" class="field-error"><?= h($errores['marca_id'] ?? '') ?></small>
        </div>
        <div class="field">
            <label for="precio">Precio ($)</label>
            <input id="precio" name="precio" type="number" min="0" max="9999999999.99" step="0.01" value="<?= h($valores['precio']) ?>" required aria-describedby="error-precio">
            <small id="error-precio" class="field-error"><?= h($errores['precio'] ?? '') ?></small>
        </div>
    </div>
    <div class="field">
        <label for="estado">Estado</label>
        <select id="estado" name="estado" required aria-describedby="error-estado">
            <option value="activo" <?= $valores['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
            <option value="inactivo" <?= $valores['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select>
        <small id="error-estado" class="field-error"><?= h($errores['estado'] ?? '') ?></small>
        <small class="muted">Inactivo conserva el registro; eliminar lo borra de la base.</small>
    </div>
    <div class="form-actions">
        <button class="button" type="submit" <?= !$marcas ? 'disabled' : '' ?>><?= h($textoBoton) ?></button>
        <a class="button button-light" href="index.php">Cancelar</a>
    </div>
</form>
