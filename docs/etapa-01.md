# Etapa 1: base de datos

Una base de datos agrupa tablas. Una tabla tiene columnas (datos que guardamos)
y filas (registros concretos).

El proyecto ahora incluye el esquema completo autorizado por el estudiante.
Para estudiarlo sin sobrescribirlo, practicá las sentencias en la pestaña SQL
de phpMyAdmin o en tus apuntes. Revisá también [la guía SQL](SQL.md).

Primera tarea de repaso: escribí una sentencia para crear crud_productos y otra
para seleccionarla como base de trabajo. Como ya existe, usá IF NOT EXISTS.
Pistas: CREATE DATABASE IF NOT EXISTS nombre; y USE nombre;

Pegá tu intento en el chat antes de seguir. No hace falta resolver toda la etapa.

Después veremos, de a un paso:
- CREATE TABLE para definir columnas.
- PRIMARY KEY para identificar cada fila sin repetir su identificador.
- AUTO_INCREMENT para generar identificadores numéricos.
- FOREIGN KEY para exigir que la marca de un producto exista.
- INSERT para guardar filas.
- SELECT para leerlas.

Tablas previstas:
- marcas: id, marca.
- productos: id, nombre, marca_id, precio, estado, fecha_carga.

La relación será productos.marca_id -> marcas.id.
El JOIN se estudia en la etapa 2.

Al crear la base, comprobarla con SHOW DATABASES;
Al seleccionarla, comprobarla con SELECT DATABASE();

Usamos root en phpMyAdmin para administrar. PHP utiliza crud_usuario, no root.
Docker y el helper de preparación ya conceden permisos sobre la base configurada.
