#!/usr/bin/env python3
"""Pruebas HTTP reales, sin librerías externas. Solo borra los registros que crea."""
import html
import http.cookiejar
import os
import re
import uuid
from urllib.error import HTTPError
from urllib.parse import urlencode, urlparse, parse_qs
from urllib.request import build_opener, HTTPCookieProcessor, Request

BASE = os.environ.get("CRUD_BASE_URL", "http://127.0.0.1:8080").rstrip("/")
browser = build_opener(HTTPCookieProcessor(http.cookiejar.CookieJar()))
checks = 0


def request(path, data=None):
    """La cookie mantiene la sesión y el token entre GET y POST, como un navegador."""
    payload = None if data is None else urlencode(data).encode()
    try:
        with browser.open(Request(BASE + "/" + path, data=payload), timeout=15) as response:
            return response.status, response.read().decode(), response.geturl()
    except HTTPError as error:
        return error.code, error.read().decode(), error.geturl()


def check(condition, description):
    global checks
    if not condition:
        raise AssertionError(description)
    checks += 1
    print(f"OK {checks:02d}: {description}")


def token(page):
    match = re.search(r'name="csrf" value="([a-f0-9]+)"', page)
    if not match:
        raise AssertionError("No se encontró el token CSRF")
    return match.group(1)


def brand_id(page, name):
    for row in re.findall(r"<tr>.*?</tr>", page, re.S):
        if html.escape(name) in row:
            match = re.search(r"marcas.php\?editar=(\d+)", row)
            if match:
                return int(match.group(1))
    raise AssertionError("La marca de prueba no apareció en el listado")


# El sufijo evita colisiones con datos del estudiante y con otras ejecuciones.
suffix = uuid.uuid4().hex[:12]
brand_name = "Prueba " + suffix
product_name = '<b>Producto ' + suffix + '</b>'
brand = None
product = None

try:
    status, page, _ = request("index.php")
    check(status == 200 and "Listado" in page, "El listado responde")
    status, page, _ = request("new.php")
    csrf = token(page)
    check(status == 200 and 'name="marca_id"' in page, "Formulario de producto disponible")
    status, _, _ = request("script.php")
    check(status == 405, "GET no ejecuta escrituras")
    status, _, _ = request("script.php", {"accion": "crear_marca", "marca": brand_name})
    check(status == 403, "POST sin token es rechazado")
    status, _, _ = request("script.php", {"csrf": "incorrecto", "accion": "crear_marca", "marca": brand_name})
    check(status == 403, "POST con token incorrecto es rechazado")
    status, _, _ = request("script.php", {"csrf": csrf, "accion": "otra"})
    check(status == 400, "Acción desconocida es rechazada")

    status, page, _ = request("script.php", {"csrf": csrf, "accion": "crear_marca", "marca": brand_name})
    brand = brand_id(page, brand_name)
    check(status == 200 and "Marca creada correctamente" in page, "Alta de marca")
    status, page, _ = request("script.php", {"csrf": csrf, "accion": "crear_marca", "marca": brand_name})
    check(status == 200 and "Ya existe una marca" in page, "Marca duplicada conserva formulario y muestra error")
    status, page, _ = request("script.php", {"csrf": csrf, "accion": "editar_marca", "id": brand, "marca": brand_name + " editada"})
    brand_name += " editada"
    check(status == 200 and "Marca actualizada correctamente" in page, "Edición de marca")

    values = {"csrf": csrf, "accion": "crear_producto", "nombre": product_name, "marca_id": brand, "precio": "1234.50", "estado": "activo"}
    status, page, _ = request("script.php", dict(values, precio="-1"))
    check(status == 200 and "Ingresá un precio" in page and html.escape(product_name, quote=True) in page, "Precio negativo rechazado; nombre conservado")
    status, page, _ = request("script.php", dict(values, precio="1.234"))
    check(status == 200 and "Ingresá un precio" in page, "Más de dos decimales es rechazado")
    status, page, _ = request("script.php", dict(values, marca_id="4294967295"))
    check(status == 200 and "Seleccioná una marca que exista" in page, "Marca inexistente es rechazada")
    status, page, _ = request("script.php", dict(values, estado="desconocido"))
    check(status == 200 and "Seleccioná activo o inactivo" in page, "Estado desconocido es rechazado")
    status, page, _ = request("script.php", dict(values, nombre=""))
    check(status == 200 and "entre 1 y 150 caracteres" in page, "Nombre vacío es rechazado")

    status, page, url = request("script.php", values)
    # Conservamos el ID antes de las assertions para poder limpiar si alguna falla.
    product = int(parse_qs(urlparse(url).query)["id"][0])
    check(status == 200 and "Producto creado correctamente" in page and brand_name in page, "Alta y vista individual con JOIN")
    check(html.escape(product_name) in page and product_name not in page, "HTML ingresado se muestra escapado")
    check("$ 1.234,50" in page, "Precio se muestra con formato decimal")
    status, page, _ = request("edit.php?id=" + str(product))
    check(status == 200 and f'value="{brand}" selected' in page and 'value="1234.50"' in page, "Edición precarga marca y precio")
    status, page, _ = request("script.php", dict(values, accion="editar_producto", id=product, precio="2500,75", estado="inactivo"))
    check(status == 200 and "Cambios guardados" in page and "$ 2.500,75" in page and "Inactivo" in page, "UPDATE guarda precio con coma y nuevo estado")
    status, page, _ = request("index.php?" + urlencode({"q": suffix, "estado": "inactivo", "marca_id": brand}))
    check(status == 200 and html.escape(product_name) in page, "Búsqueda y filtros combinados encuentran producto")
    status, page, _ = request("index.php?" + urlencode({"q": suffix, "estado": "activo"}))
    check(status == 200 and "No hay productos para mostrar" in page, "Filtro activo excluye el producto inactivo")
    status, page, _ = request("index.php?" + urlencode({"q": "' OR 1=1 --"}))
    check(status == 200 and "No hay productos para mostrar" in page, "La búsqueda trata SQL ingresado como un valor")
    status, page, _ = request("script.php", {"csrf": csrf, "accion": "eliminar_marca", "id": brand})
    check(status == 200 and "tiene productos asociados" in page, "La FK bloquea borrar una marca en uso")
    status, page, _ = request("delete.php?id=" + str(product))
    check(status == 200 and "Sí, eliminar producto" in page, "GET muestra confirmación de borrado")
    status, page, _ = request("show.php?id=" + str(product))
    check(status == 200 and html.escape(product_name) in page, "Visitar confirmación no borró el producto")
    for invalid in ["", "abc", "-1", "4294967295", "1 OR 1=1", "1&id[]=2"]:
        status, _, _ = request("show.php?id=" + invalid.replace(" ", "%20"))
        check(status == 404, "ID inválido o ausente responde 404: " + repr(invalid))
    status, page, _ = request("marcas.php?eliminar=" + str(brand))
    check(status == 200 and "Sí, eliminar marca" in page, "Marca también tiene confirmación")

    status, page, _ = request("script.php", {"csrf": csrf, "accion": "eliminar_producto", "id": product})
    check(status == 200 and "Producto eliminado correctamente" in page, "DELETE mediante POST confirmado")
    status, _, _ = request("show.php?id=" + str(product))
    check(status == 404, "El producto eliminado ya no existe")
    product = None
    status, page, _ = request("script.php", {"csrf": csrf, "accion": "eliminar_marca", "id": brand})
    check(status == 200 and "Marca eliminada correctamente" in page and brand_name not in page, "Marca sin productos se elimina")
    brand = None
finally:
    # Incluso si una assertion falla, intentamos limpiar únicamente nuestros IDs.
    # Un fallo de red durante limpieza queda visible, no se describe como éxito.
    if product is not None or brand is not None:
        _, page, _ = request("new.php")
        cleanup_token = token(page)
        for action, record in [("eliminar_producto", product), ("eliminar_marca", brand)]:
            if record is not None:
                status, body, _ = request("script.php", {"csrf": cleanup_token, "accion": action, "id": record})
                if status != 200 or "eliminad" not in body:
                    raise RuntimeError(f"No se pudo limpiar {action} #{record}; revisar manualmente")

print(f"\n{checks} comprobaciones pasaron. Registros de prueba eliminados.")
