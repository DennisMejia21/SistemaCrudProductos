# CRUD de productos y marcas

Proyecto de práctica completo con PHP 8.3, PDO, MariaDB 11.4, HTML y CSS propio.
Mantiene los archivos usados en clase, con comentarios para seguir el recorrido
de cada consulta y formulario. Sin frameworks ni dependencias de un CDN.

## Qué podés hacer

- Crear, listar, ver, editar y eliminar productos.
- Crear, editar y eliminar marcas; no permite borrar una marca en uso.
- Buscar por producto/marca y filtrar por marca y estado.
- Ver resumen del catálogo, precios y fechas de carga.
- Usar formularios precargados, mensajes y validaciones que conservan los campos.
- Estudiar consultas preparadas, JOIN, sesiones, escape HTML y tokens CSRF.

Es una aplicación de práctica local sin login ni roles. Para un despliegue público
se necesita agregar autenticación, autorización y configuración de producción.

## Arranque en tu computadora

Requisitos: Git, Docker y Docker Compose (`docker compose`).
Si todavía no tenés el repo:

```bash
git clone git@github.com:DennisMejia21/SistemaCrudProductos.git
cd SistemaCrudProductos
```

Si ya lo tenés, usá ese checkout. Crear `.env` solo si todavía no existe:

```bash
cp .env.example .env
docker compose up -d --build --wait --wait-timeout 120
docker compose ps
```

En un volumen nuevo la base, las tablas y cuatro ejemplos se crean automáticamente.
Si conservás el volumen del entorno inicial, completá su esquema sin borrar datos:

```bash
bash scripts/init-db.sh --seed
docker compose up -d --wait --wait-timeout 120
```

En tu computadora, abrí `localhost:8080` para el catálogo y `localhost:8081` para
phpMyAdmin. Usá los puertos de `.env` si los cambiaste. Para administrar en
phpMyAdmin, el usuario es root y la contraseña es DB_ROOT_PASSWORD de tu `.env`.
Para consultar como la aplicación, usá DB_USER y DB_PASSWORD.
No publiques las credenciales de práctica ni subas `.env` a GitHub.

## Estructura

```text
SistemaCrudProductos/
├── AGENTS.md
├── Dockerfile
├── compose.yaml
├── .env.example          # plantilla compartida
├── .env                  # local, ignorado
├── .gitignore
├── .dockerignore
├── database/
│   ├── database.sql      # tablas y restricciones
│   └── seed.sql          # datos de ejemplo
├── scripts/init-db.sh    # preparación sin borrar el volumen
├── tests/smoke.py        # pruebas HTTP
├── docs/                 # guías de estudio y comandos
└── src/                  # carpeta pública que sirve Apache
    ├── conexion.php
    ├── funciones.php
    ├── script.php
    ├── index.php
    ├── show.php
    ├── new.php
    ├── edit.php
    ├── delete.php
    ├── marcas.php
    ├── partials/
    │   ├── header.php
    │   ├── footer.php
    │   └── formulario-producto.php
    └── assets/css/estilos.css
```

La relación es `productos.marca_id → marcas.id`. Cada producto tiene una marca;
cada marca puede tener muchos productos. Los SELECT con JOIN muestran el nombre
de marca sin duplicarlo en cada producto.

## Documentación para aprender

| Guía | Contenido |
| --- | --- |
| [Código explicado](docs/GUIA-CODIGO.md) | Todos los archivos, funciones, GET/POST, PDO, JOIN, validaciones y ejercicios. |
| [SQL](docs/SQL.md) | CREATE DATABASE, INSERT, SELECT, UPDATE, DELETE y relaciones. |
| [Docker](docs/DOCKER.md) | Conceptos, comandos habituales, backups y solución de errores. |
| [Git](docs/GIT.md) | Commits pequeños, identidad, ramas y envío al remoto. |
| [Pruebas](docs/PRUEBAS.md) | Smoke test automático y recorrido manual. |
| [Etapa 1](docs/etapa-01.md) | Primer ejercicio de repaso de base de datos. |

## Comandos rápidos

```bash
docker compose ps
docker compose logs --tail=50 web db
docker compose stop
docker compose up -d --wait
python3 tests/smoke.py
git status
git diff
```

`python3` solo es necesario para las pruebas, no para ejecutar la aplicación.
Los datos están en `db_data`. `docker compose down` conserva ese volumen;
agregar `-v` lo elimina y borra los registros. Cambiar la contraseña en `.env`
no cambia la contraseña de una base ya inicializada.

En la nube, el setup utiliza `DOCKER_CONFIG=/workspace/.docker-crud` por los
permisos de la carpeta predeterminada. Los procesos deben volver a arrancarse
en cada instancia; las instrucciones reutilizables están guardadas en el entorno.
La publicación y el envío de commits a GitHub son pasos separados.
