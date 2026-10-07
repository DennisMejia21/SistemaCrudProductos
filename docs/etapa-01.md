# Etapa 1: base de datos

Una base de datos agrupa tablas. Una tabla tiene columnas (datos que guardamos)
y filas (registros concretos).

Primera tarea: escribir en database/database.sql una sentencia para crear
crud_productos y otra para seleccionarla como base de trabajo.
Pistas: CREATE DATABASE nombre; y USE nombre;

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

Usamos root en phpMyAdmin solamente para crear la base y conceder permisos
al usuario de práctica. PHP utilizará crud_usuario, no root.
Cuando terminemos CREATE DATABASE, configuraremos esos permisos juntos.
