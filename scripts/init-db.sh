#!/usr/bin/env bash
# Ejecutar desde cualquier carpeta. No elimina bases, tablas ni volúmenes.
set -euo pipefail
cd "$(dirname "$0")/.."
docker compose up -d --wait --wait-timeout 120 db

# MariaDB no repite su inicialización automática en un volumen antiguo.
# Validar nombres evita que se interpreten como fragmentos arbitrarios de SQL.
docker compose exec -T db sh -eu -c '
  case "$MARIADB_DATABASE" in ""|*[!a-zA-Z0-9_]*) echo "DB_NAME solo admite letras, números y _" >&2; exit 1;; esac
  case "$MARIADB_USER" in ""|*[!a-zA-Z0-9_]*) echo "DB_USER solo admite letras, números y _" >&2; exit 1;; esac
  export MYSQL_PWD="$MARIADB_ROOT_PASSWORD"
  mariadb --user=root -e "CREATE DATABASE IF NOT EXISTS \`$MARIADB_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL PRIVILEGES ON \`$MARIADB_DATABASE\`.* TO '\''$MARIADB_USER'\''@'\''%'\'';"
'

# Usar la contraseña dentro del contenedor evita imprimirla o guardarla en Git.
docker compose exec -T db sh -eu -c 'MYSQL_PWD="$MARIADB_ROOT_PASSWORD" mariadb --user=root --database="$MARIADB_DATABASE"' < database/database.sql
echo "Esquema preparado sin borrar datos."

# Los ejemplos son opcionales en un volumen existente.
if [ "${1:-}" = "--seed" ]; then
  docker compose exec -T db sh -eu -c 'MYSQL_PWD="$MARIADB_ROOT_PASSWORD" mariadb --user=root --database="$MARIADB_DATABASE"' < database/seed.sql
  echo "Datos de ejemplo agregados sin duplicar nombres existentes."
fi
