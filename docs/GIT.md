# Git: guardar avances pequeños

Git conserva versiones locales; GitHub aloja una copia remota. Un commit tiene
cambios, mensaje y autor. La identidad configurada se usa automáticamente.

## Comandos frecuentes

| Comando | Qué hace |
| --- | --- |
| `git status` | Muestra rama y archivos modificados o pendientes. |
| `git diff` | Muestra cambios que todavía no seleccionaste. |
| `git add ruta/al/archivo` | Selecciona ese archivo para el próximo commit. |
| `git diff --cached` | Revisa exactamente los cambios seleccionados. |
| `git diff --cached --check` | Detecta problemas de espacios en lo seleccionado. |
| `git commit -m "mensaje"` | Guarda una versión local con mensaje. |
| `git log --oneline -8` | Muestra los últimos commits. |
| `git show --stat HEAD` | Resume los archivos del último commit. |
| `git branch --show-current` | Muestra la rama actual. |
| `git switch -c practicar-filtros` | Crea una rama para un ejercicio. |
| `git restore --staged archivo` | Quita un archivo de la selección sin borrar sus cambios. |
| `git check-ignore .env` | Comprueba que `.env` está ignorado. |
| `git fetch origin` | Actualiza referencias remotas sin modificar tus archivos. |
| `git pull --ff-only` | Actualiza la rama sin crear una fusión automática. |

`git add .` selecciona todo lo que no esté ignorado. Al aprender, preferí rutas
específicas para que cada commit tenga un tema claro.

## Identidad

```bash
git config --get user.name
git config --get user.email
```

Si necesitás configurarla para este repositorio:

```bash
git config user.name "Tu nombre"
git config user.email "Tu correo de GitHub"
```

El correo debería estar asociado a tu cuenta de GitHub o ser el correo privado
que GitHub te muestra en sus ajustes. No pongas tokens ni contraseñas en commits.
`.env.example` es una plantilla de práctica; `.env` queda solamente en tu máquina.

## Ejemplo de un commit de aprendizaje

Supongamos que agregaste un filtro a la consulta de productos. Lo que guardás
es ese cambio y su explicación, no todo tu directorio de trabajo:

```bash
git status
git diff
git add src/funciones.php src/index.php
git diff --cached
git commit -m "agregar filtro de productos por estado"
```

Un commit exitoso todavía no publica nada en GitHub. Para subir una rama cuando
vos decidas hacerlo:

```bash
git branch --show-current
git push -u origin NOMBRE_DE_TU_RAMA
```

Reemplazá el marcador por la rama real. Si estás en `work`, no supongas que es
`main`. Podés subir la rama y abrir un pull request para revisar sus cambios.
La autenticación HTTPS de la nube la administra la plataforma; no hace falta
extraer ni copiar sus credenciales. En tu computadora podés utilizar tu SSH habitual.

## Antes de guardar o subir

Revisá el diff, ejecutá las pruebas correspondientes y comprobá que no incluís
`.env`, backups o registros personales. Evitá `git reset --hard` para arreglar
problemas sin entenderlo: puede eliminar cambios que todavía no guardaste.

Los commits de este trabajo se agrupan por base/Docker, CRUD y documentación/pruebas.
No se enviaron al remoto automáticamente.
