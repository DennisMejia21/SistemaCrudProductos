# SistemaCrudProductos

Práctica guiada de CRUD con PHP, PDO y MariaDB. Estado: entorno inicial.
Las tablas y el CRUD todavía no están implementados.

## Preparar el entorno

Requisitos: Git, Docker y Docker Compose (comando docker compose).

Si todavía no tenés el repositorio:
```bash
git clone git@github.com:DennisMejia21/SistemaCrudProductos.git
cd SistemaCrudProductos
```

Si ya lo tenés, entrá a esa carpeta y actualizá main con git pull --ff-only
después de revisar git status y guardar cualquier cambio propio.

Crear la configuración local (solo la primera vez):
```bash
cp .env.example .env
docker compose up -d --build
docker compose ps
```

- Página de práctica: http://localhost:8080
- phpMyAdmin: http://localhost:8081
- Inicio de sesión inicial en phpMyAdmin: root y el valor DB_ROOT_PASSWORD de .env.

.env contiene configuración local y no se sube a GitHub.
.env.example es una plantilla con credenciales ficticias de práctica.
Si los puertos están ocupados, modificá APP_PORT o PMA_PORT en .env.

## Qué prepara Docker

- Dockerfile: PHP 8.3 con Apache y la extensión pdo_mysql.
- compose.yaml: coordina web, db y phpmyadmin.
- src/: carpeta pública servida por Apache, con los nombres utilizados en clase.
- db_data: volumen que conserva los datos entre reinicios.
- DB_HOST será db: dentro de Docker, es el nombre del servicio de base de datos.

No se crea automáticamente crud_productos para poder practicar CREATE DATABASE.
MariaDB crea sus tablas internas y el usuario crud_usuario, inicialmente sin
permisos sobre nuestra futura base. Después de crearla aprenderemos a otorgarlos.
Las variables de inicialización de MariaDB solo se aplican con un volumen nuevo;
editar la contraseña en .env no cambia la de una base ya inicializada.

## Archivos

| Archivo | Para qué servirá |
| --- | --- |
| src/index.php | Listado; hoy solo muestra la página de bienvenida |
| src/conexion.php | Conexión PDO, etapa 3 |
| src/show.php | Un producto, etapa 5 |
| src/new.php | Formulario de alta, etapa 6 |
| src/script.php | Procesar acciones de formularios |
| src/edit.php | Formulario de edición, etapa 7 |
| src/delete.php | Confirmar eliminación, etapa 8 |
| src/funciones.php | Funciones reutilizables, etapa 9 |
| src/assets/css/estilos.css | Estilos básicos |
| database/database.sql | Consultas que escribirá el estudiante |
| docs/etapa-01.md | Primera tarea y conceptos |
| AGENTS.md | Reglas para mantener la enseñanza paso a paso |

Los PHP pendientes contienen únicamente comentarios.
database.sql no está montado como script de inicialización: lo ejecutaremos
manualmente en la pestaña SQL de phpMyAdmin, por partes y después de revisarlo.

## Primera tarea

Leé docs/etapa-01.md. Escribí CREATE DATABASE y USE en database/database.sql
y compartí el intento antes de continuar.

## Detener y diagnosticar

```bash
docker compose stop
docker compose start
docker compose logs --tail=50 web db
```

docker compose down también conserva el volumen. No agregues -v:
esa opción elimina los datos guardados.

## Git paso a paso

El primer commit guarda el entorno y los archivos pendientes, no el CRUD.
Después de revisar cada ejercicio, guardaremos únicamente sus cambios.
Por ejemplo, al terminar la tabla marcas:

```bash
git diff
git add database/database.sql
git commit -m "crear tabla marcas"
```

git add selecciona cambios; git commit crea una versión local;
git push la envía a GitHub. No guardes credenciales en los commits.

## Verificación pendiente

El entorno debe probarse con Docker en la computadora del estudiante.
La página inicial debe abrir y phpMyAdmin debe permitir iniciar sesión.
Todavía no corresponde probar conexión PDO, tablas ni operaciones CRUD.
