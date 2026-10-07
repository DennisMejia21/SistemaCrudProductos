# Docker: conceptos y comandos frecuentes

Ejecutá los comandos desde la raíz de `SistemaCrudProductos`. En Windows, usá
Docker Desktop con contenedores Linux; para el helper `.sh`, usá Git Bash o WSL.
No necesitás instalar PHP ni MariaDB en tu computadora.

## Qué significa cada parte

| Concepto | En este proyecto |
| --- | --- |
| Imagen | Plantilla con el programa y sus dependencias: PHP, MariaDB o phpMyAdmin. |
| Contenedor | Proceso que ejecuta una imagen. |
| Dockerfile | Receta para construir nuestra imagen PHP con `pdo_mysql`. |
| Compose | Define los tres servicios y cómo se conectan. |
| Bind mount | `./src:/var/www/html`: Apache lee directamente los archivos que editás. |
| Volumen | `db_data`: guarda los archivos de la base aunque cambie el contenedor. |
| Red | Los servicios se encuentran por nombre: PHP usa `db`, no `localhost`. |
| Puerto | `8080:80`: el puerto 8080 de tu computadora llega al 80 del contenedor web. |
| Healthcheck | MariaDB informa si puede aceptar conexiones y terminó su preparación. |

Los puertos web están ligados a `127.0.0.1`, así que son locales. MariaDB no publica
3306 en la computadora: PHP y phpMyAdmin llegan por la red interna de Compose.
`depends_on` espera a que la base esté saludable; no garantiza que todas las
tablas de un volumen antiguo ya estén preparadas.

## Primer arranque

```bash
# Hacerlo una sola vez; conservar .env si ya existe.
cp .env.example .env

# Construye PHP y arranca los servicios en segundo plano.
docker compose up -d --build --wait --wait-timeout 120
docker compose ps
```

En un volumen nuevo, MariaDB crea `DB_NAME`, el usuario y los datos de ejemplo.
En tu computadora, la aplicación usa `localhost:8080` y phpMyAdmin
`localhost:8081`. Si modificaste APP_PORT/PMA_PORT, usá esos puertos.
Estos son accesos locales; no son enlaces de vista previa de la nube.

## Si ya existía un volumen sin tablas

MariaDB ejecuta `/docker-entrypoint-initdb.d` **solo la primera vez**. No hace falta
eliminar el volumen para incorporar el esquema:

```bash
bash scripts/init-db.sh --seed
docker compose up -d --wait --wait-timeout 120
```

Sin `--seed` prepara únicamente esquema y permisos. Con `--seed` agrega ejemplos
ausentes por nombre. Puede reponer un ejemplo que borraste; no uses esa opción en
cada arranque normal. El helper no borra filas ni modifica contraseñas.
Si el usuario del volumen no coincide con DB_USER, habrá que administrar ese
usuario: cambiar `.env` no recrea usuarios ni cambia claves ya guardadas.

## Uso cotidiano

| Comando | Para qué sirve |
| --- | --- |
| `docker compose up -d --wait` | Crear/actualizar contenedores y esperar el arranque. |
| `docker compose ps` | Ver estado y puertos del proyecto. |
| `docker compose logs --tail=50 web db` | Ver los últimos mensajes de PHP/Apache y MariaDB. |
| `docker compose logs -f web` | Seguir los logs; Ctrl+C deja de mirarlos, no apaga la aplicación. |
| `docker compose stop` | Detener los servicios conservando contenedores y datos. |
| `docker compose start` | Arrancar contenedores ya creados sin aplicar cambios de configuración. |
| `docker compose restart web` | Reiniciar únicamente PHP/Apache. |
| `docker compose down` | Quitar contenedores y red conservando el volumen de la base. |
| `docker compose up -d --build web` | Reconstruir PHP después de cambiar Dockerfile. |
| `docker compose build web` | Construir la imagen sin iniciar servicios. |
| `docker compose pull db phpmyadmin` | Descargar las imágenes de esos servicios. |
| `docker compose exec web bash` | Abrir una terminal dentro del contenedor web. |
| `docker compose exec db mariadb -u root -p` | Abrir SQL e ingresar la clave cuando se solicite. |
| `docker compose exec web php -v` | Comprobar la versión de PHP. |
| `docker compose exec web php -m` | Ver extensiones, incluyendo `PDO` y `pdo_mysql`. |
| `docker compose exec web php -l /var/www/html/index.php` | Revisar sintaxis de un archivo PHP. |
| `docker compose config --quiet` | Validar Compose sin imprimir las contraseñas resueltas. |
| `docker ps` | Ver contenedores activos, incluidos los de otros proyectos. |
| `docker images` | Ver imágenes descargadas o construidas. |
| `docker volume ls` | Ver los volúmenes, sin borrarlos. |

Para cambios en PHP/CSS solo guardá el archivo y actualizá el navegador. Para
cambios en Dockerfile reconstruí la imagen. Para cambios en Compose o `.env`,
usá `up -d`, porque `restart` no aplica nuevas variables.
Las claves de una base ya inicializada siguen requiriendo cambio dentro de MariaDB.

## Variables de .env

| Variable | Uso |
| --- | --- |
| `APP_PORT` | Puerto local del catálogo; inicialmente 8080. |
| `PMA_PORT` | Puerto local de phpMyAdmin; inicialmente 8081. |
| `DB_NAME` | Nombre de la base; se pasa como MARIADB_DATABASE y como DB_NAME a PHP. |
| `DB_USER` | Usuario de práctica que usa PHP, distinto de root. |
| `DB_PASSWORD` | Contraseña local del usuario de práctica. |
| `DB_ROOT_PASSWORD` | Contraseña local del administrador MariaDB. |

`DB_HOST` no se escribe en `.env`: Compose lo fija como `db` para PHP.
La contraseña de root no se inyecta en el servicio web. Cambiar los valores de
la plantilla no modifica tu `.env`; copiala únicamente en la primera preparación.

## Backup y restauración

Crear una copia usando las credenciales dentro del contenedor:

```bash
mkdir -p backups
docker compose exec -T db sh -eu -c 'MYSQL_PWD="$MARIADB_ROOT_PASSWORD" mariadb-dump --single-transaction --user=root "$MARIADB_DATABASE"' > backups/crud.sql
```

Confirmá que el comando terminó correctamente y que el archivo no está vacío.
`backups/` está ignorado por Git; guardá las copias en un lugar propio y seguro.
Antes de restaurar, hacé otra copia del estado actual. La restauración reemplaza
las tablas incluidas en el dump; usala solo si querés recuperar ese estado:

```bash
docker compose exec -T db sh -eu -c 'MYSQL_PWD="$MARIADB_ROOT_PASSWORD" mariadb --user=root --database="$MARIADB_DATABASE"' < backups/crud.sql
```

No agregues `-v` a `docker compose down`: elimina el volumen y sus registros.
Tampoco uses limpieza global de volúmenes para resolver un error de este proyecto.

## Diagnóstico de problemas

| Síntoma | Qué revisar |
| --- | --- |
| Puerto ocupado | Cambiar APP_PORT o PMA_PORT en `.env` y ejecutar `up -d`. |
| Base no encontrada | Ejecutar `bash scripts/init-db.sh` y revisar DB_NAME. |
| Access denied | El volumen conserva la contraseña anterior; corregir configuración o administrar el usuario. |
| Connection refused | Estado de db, logs y DB_HOST. Dentro de Docker debe ser `db`. |
| Página 403 | Permisos de lectura y recorrido de `src/` en Linux. |
| Página de error 500 | Logs web/db, conexión y existencia de las tablas. |
| Cambios CSS no aparecen | Recarga completa del navegador y verificar que estás editando `src/`. |

Si el entorno en la nube tiene la carpeta predeterminada de Docker de solo lectura:

```bash
export DOCKER_CONFIG=/workspace/.docker-crud
mkdir -p "$DOCKER_CONFIG"
```

En esta nube los archivos se restauraron con permisos muy restrictivos. El setup
aplica lectura a `src/` y a los SQL montados; no hace público `.env`:

```bash
find src database -type d -exec chmod 755 {} +
find src database -type f -exec chmod 644 {} +
chmod 600 .env
```

Estos ajustes son del entorno; no cambian el contenido de los archivos del proyecto.
