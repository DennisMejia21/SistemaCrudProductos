# SQL para practicar

Entrá a phpMyAdmin con root y la contraseña local de `.env` para administrar.
Para consultas normales podés usar el usuario `DB_USER`, que tiene permisos
solamente sobre la base de práctica. Seleccioná esa base y abrí la pestaña SQL.
No pegues tus contraseñas en chats ni las guardes en archivos versionados.

## Crear y elegir una base

```sql
CREATE DATABASE IF NOT EXISTS crud_productos
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crud_productos;
SHOW TABLES;
SELECT DATABASE();
```

Docker ya lo prepara por vos en un volumen nuevo. Leé `database/database.sql`
para CREATE TABLE, PRIMARY KEY, AUTO_INCREMENT, FOREIGN KEY y restricciones.
`IF NOT EXISTS` evita recrear una tabla existente; no actualiza automáticamente
las columnas si más adelante cambiás su definición.

## Leer datos

```sql
SELECT * FROM marcas;
SELECT id, nombre, marca_id, precio, estado FROM productos;
SELECT nombre, precio FROM productos WHERE estado = 'activo';
SELECT nombre, precio FROM productos ORDER BY precio DESC;

SELECT p.nombre, m.marca, p.precio
FROM productos p
INNER JOIN marcas m ON p.marca_id = m.id
WHERE m.marca = 'Sony';
```

En el ejemplo anterior, `WHERE` filtra y `ORDER BY` ordena. `INNER JOIN` relaciona
filas. La clave foránea impide que un producto quede apuntando a una marca ausente.

## Agregar filas

```sql
INSERT INTO marcas (marca) VALUES ('Marca de práctica');

-- Seleccionamos el ID real; los IDs no siempre empiezan en 1 ni son consecutivos.
SELECT id FROM marcas WHERE marca = 'Marca de práctica';

-- Ejemplo: si el SELECT anterior devolvió 12, usar 12 como marca_id.
INSERT INTO productos (nombre, marca_id, precio, estado)
VALUES ('Producto de práctica', 12, 1500.50, 'activo');
```

Reemplazá el ID del ejemplo por el de tu marca. No escribas `$` ni puntos de miles
en el valor SQL del precio. `fecha_carga` se completa sola.

## Cambiar o eliminar una fila

Primero hacé un SELECT para confirmar el registro. Estos ejemplos usan ID 7:
cambialo por el ID que vos creaste, no por uno de otra persona.

```sql
SELECT * FROM productos WHERE id = 7;
UPDATE productos SET precio = 1900.75, estado = 'inactivo' WHERE id = 7;
SELECT * FROM productos WHERE id = 7;
```

Para practicar DELETE sin perder el registro de forma permanente:

```sql
START TRANSACTION;
DELETE FROM productos WHERE id = 7;
SELECT * FROM productos WHERE id = 7; -- Ya no aparece en esta transacción.
ROLLBACK; -- Recupera la fila porque no confirmamos la transacción.
```

Ejecutá ese bloque completo en la misma conexión. `COMMIT` confirmaría la
eliminación. No uses DROP, TRUNCATE o un DELETE sin WHERE para probar el CRUD.

## Claves y relaciones

```sql
SHOW CREATE TABLE productos;
SHOW CREATE TABLE marcas;

SELECT m.marca, COUNT(p.id) AS cantidad
FROM marcas m
LEFT JOIN productos p ON p.marca_id = m.id
GROUP BY m.id, m.marca;
```

`LEFT JOIN` muestra también las marcas sin productos, con cantidad 0.
Ejercicio: modificá el INNER JOIN para mostrar solo productos activos de Sony.
Después explicá qué partes son relación y cuáles son filtros.
