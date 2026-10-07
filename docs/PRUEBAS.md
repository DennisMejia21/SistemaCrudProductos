# Cómo probar el proyecto

## Pruebas automáticas

Con los servicios arriba y la base preparada, necesitás Python 3 para el smoke
test. No instala librerías ni necesita PHP local:

```bash
python3 tests/smoke.py
```

En Windows puede ser `py -3 tests/smoke.py`. Si cambiaste APP_PORT, en Bash:

```bash
CRUD_BASE_URL=http://127.0.0.1:8090 python3 tests/smoke.py
```

Se ejecutan 35 comprobaciones contra la aplicación real: respuestas de páginas,
alta/edición/borrado de marcas y productos, validación, JOIN, filtros, HTML
escapado, búsqueda con texto SQL, IDs inválidos, confirmación y token CSRF.
El resultado termina con `35 comprobaciones pasaron` y un código de salida 0.
Un AssertionError o código distinto de 0 es un fallo, aunque algunos pasos hayan pasado.

Se crean registros con un nombre aleatorio y se eliminan al terminar, también ante
un fallo normal. No se borran productos ajenos. Si se corta la conexión o se mata
el proceso antes de la limpieza, revisá la marca `Prueba ...` que dejó esa ejecución.
AUTO_INCREMENT puede avanzar aunque se borren las filas de prueba; es normal.

La revisión de sintaxis PHP es complementaria; no reemplaza los flujos reales:

```bash
docker compose exec -T web sh -c 'find /var/www/html -name "*.php" -exec php -l {} \;'
```

## Comprobación manual

1. **Listado:** verificá nombre de marca, precio, estado y enlaces de acciones.
2. **Marca:** creá `Mi marca`; intentá crearla de nuevo y observá el error.
3. **Alta:** elegí la marca nueva, cargá un precio y guardá el producto.
4. **Detalle:** Ver debe mostrar solo ese producto y su marca.
5. **Edición:** cambiá precio/estado y verificá el detalle; la fecha se conserva.
6. **Filtros:** buscá por nombre, marca y estado; Limpiar vuelve al listado completo.
7. **Relación:** intentá borrar la marca usada; debe rechazarse sin perder datos.
8. **Confirmación:** entrá a Eliminar producto y cancelá; debe seguir existiendo.
9. **Borrado:** confirmá por formulario; el detalle anterior debe responder 404.
10. **Marca libre:** borrá la marca que creaste cuando ya no tenga productos.
11. **Persistencia:** `docker compose stop`, luego `docker compose up -d --wait`;
    los datos propios deben seguir ahí.
12. **Presentación:** revisá escritorio y móvil, navegación con Tab, labels y campos.

## Diagnosticar un fallo

```bash
docker compose ps
docker compose logs --tail=50 web db
```

Un 403 al enviar POST suele ser un token inválido: recargá el formulario.
Un 404 indica un ID mal formado o que ya no existe.
Un 405 en script.php indica que se intentó abrirlo por GET.
Un 500 requiere revisar conexión, esquema y logs; no significa que se guardó la acción.

El smoke test comprueba el servidor y el HTML, pero no renderiza una captura visual,
no prueba cargas masivas ni simula todas las condiciones de concurrencia.

En la verificación inicial también se ejecutó el smoke test en un proyecto Compose
con volumen nuevo. Se compararon los registros antes y después de detener/arrancar
la base y de repetir el helper sin `--seed`: se conservaron. Se revisaron listado,
formulario y marcas en Chromium, con vistas de escritorio y móvil.
