<?php
// Docker pasa estas variables de .env al proceso PHP. No guardamos claves en código.
$host = getenv('DB_HOST') ?: 'db';
$base = getenv('DB_NAME') ?: 'crud_productos';
$usuario = getenv('DB_USER') ?: 'crud_usuario';
$clave = getenv('DB_PASSWORD');

// DSN indica controlador, servidor, base y codificación. "db" es el servicio Docker.
$dsn = "mysql:host={$host};dbname={$base};charset=utf8mb4";

// $pdo es el objeto de conexión; las funciones lo reciben para ejecutar SQL.
$pdo = new PDO($dsn, $usuario, $clave === false ? '' : $clave, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Los errores se pueden capturar.
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Filas con claves como 'nombre'.
    PDO::ATTR_EMULATE_PREPARES => false, // MariaDB separa la consulta de sus valores.
]);
