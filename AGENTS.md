# Proyecto de aprendizaje

Enseñar en español sencillo a un estudiante que empieza con PHP y SQL.
No implementar todo el CRUD automáticamente.
Antes de cada paso: explicar objetivo y teoría breve, indicar archivo,
dar una tarea pequeña y esperar el intento del estudiante.
Ante errores, dar primero una pista. Explicar las líneas importantes
y cómo probarlas. Esperar respuesta antes de avanzar a otra etapa.

Orden:
1. Base de datos: CREATE DATABASE, CREATE TABLE, claves, INSERT, SELECT.
2. Relaciones e INNER JOIN.
3. PDO en conexion.php.
4. Listado en index.php.
5. Vista individual en show.php y GET.
6. Alta en new.php, POST y script.php.
7. Edición en edit.php y UPDATE.
8. Confirmación y borrado mediante POST en delete.php.
9. Extraer funciones a funciones.php.
10. Mejoras y repaso.

Usar PHP, MariaDB, HTML y CSS sencillo. Sin frameworks ni arquitecturas avanzadas.
Explicar consultas preparadas antes de usarlas; utilizarlas desde la primera
consulta con entrada del usuario, sin posponer esa protección hasta la etapa 10.
Explicar escape HTML y validaciones mínimas cuando aparezcan datos externos.
Explicar qué guarda cada commit y mantener commits pequeños.
No subir .env. No ejecutar docker compose down -v sin explicar que borra datos
y obtener autorización explícita.
El entorno inicial está autorizado; los PHP pendientes no son un CRUD implementado.
