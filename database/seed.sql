-- Datos de ejemplo. No borra ni modifica registros existentes.
INSERT INTO marcas (marca)
SELECT 'Sony' WHERE NOT EXISTS (SELECT 1 FROM marcas WHERE marca = 'Sony');
INSERT INTO marcas (marca)
SELECT 'Samsung' WHERE NOT EXISTS (SELECT 1 FROM marcas WHERE marca = 'Samsung');
INSERT INTO marcas (marca)
SELECT 'Apple' WHERE NOT EXISTS (SELECT 1 FROM marcas WHERE marca = 'Apple');
INSERT INTO marcas (marca)
SELECT 'Logitech' WHERE NOT EXISTS (SELECT 1 FROM marcas WHERE marca = 'Logitech');

-- Buscamos la marca por nombre: no dependemos de que Sony tenga el ID 1.
INSERT INTO productos (nombre, marca_id, precio, estado)
SELECT 'Auriculares WH-1000XM5', id, 500000.00, 'activo' FROM marcas
WHERE marca = 'Sony' AND NOT EXISTS
    (SELECT 1 FROM productos WHERE nombre = 'Auriculares WH-1000XM5');
INSERT INTO productos (nombre, marca_id, precio, estado)
SELECT 'Monitor Odyssey G5', id, 380000.00, 'activo' FROM marcas
WHERE marca = 'Samsung' AND NOT EXISTS
    (SELECT 1 FROM productos WHERE nombre = 'Monitor Odyssey G5');
INSERT INTO productos (nombre, marca_id, precio, estado)
SELECT 'Teclado Magic Keyboard', id, 150000.00, 'inactivo' FROM marcas
WHERE marca = 'Apple' AND NOT EXISTS
    (SELECT 1 FROM productos WHERE nombre = 'Teclado Magic Keyboard');
INSERT INTO productos (nombre, marca_id, precio, estado)
SELECT 'Mouse MX Master 3S', id, 95000.00, 'activo' FROM marcas
WHERE marca = 'Logitech' AND NOT EXISTS
    (SELECT 1 FROM productos WHERE nombre = 'Mouse MX Master 3S');
