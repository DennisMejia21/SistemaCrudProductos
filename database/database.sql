-- Docker crea la base definida por DB_NAME y ejecuta este archivo dentro de ella.
-- Para practicar manualmente: CREATE DATABASE crud_productos;
-- Luego: USE crud_productos; y ejecutar las sentencias siguientes.
-- IF NOT EXISTS permite repetir la preparación sin borrar tablas ni registros.

CREATE TABLE IF NOT EXISTS marcas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    marca VARCHAR(80) NOT NULL,
    -- Evita dos marcas con el mismo nombre.
    CONSTRAINT uq_marcas_nombre UNIQUE (marca)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS productos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    marca_id INT UNSIGNED NOT NULL,
    -- DECIMAL representa dinero con precisión decimal, a diferencia de FLOAT.
    precio DECIMAL(12,2) NOT NULL,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    fecha_carga DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_productos_precio CHECK (precio >= 0),
    -- La marca debe existir. RESTRICT impide borrar una marca que está en uso.
    CONSTRAINT fk_productos_marcas FOREIGN KEY (marca_id)
        REFERENCES marcas(id) ON UPDATE RESTRICT ON DELETE RESTRICT,
    INDEX idx_productos_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
