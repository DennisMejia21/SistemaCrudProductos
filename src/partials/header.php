<?php
// Plantilla compartida: una modificación actualiza todas las pantallas.
$titulo = $titulo ?? 'Productos';
$seccion = $seccion ?? 'productos';
$aviso = $_SESSION['mensaje'] ?? null;
unset($_SESSION['mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($titulo) ?> · Catálogo</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
<header class="topbar">
    <div class="container nav">
        <a class="brand" href="index.php"><span class="brand-icon">P</span> Productos <span class="brand-note">/ catálogo</span></a>
        <nav aria-label="Navegación principal">
            <a class="<?= $seccion === 'productos' ? 'active' : '' ?>" href="index.php">Productos</a>
            <a class="<?= $seccion === 'marcas' ? 'active' : '' ?>" href="marcas.php">Marcas</a>
        </nav>
    </div>
</header>
<main class="container main">
    <?php if ($aviso): ?>
        <!-- h() se usa también en mensajes para no interpretar HTML ingresado. -->
        <div class="alert <?= $aviso['tipo'] === 'error' ? 'alert-error' : 'alert-success' ?>" role="status"><?= h($aviso['texto']) ?></div>
    <?php endif; ?>
