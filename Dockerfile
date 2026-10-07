# Imagen oficial: incluye PHP y Apache, sin frameworks.
FROM php:8.3-apache
# Instala el controlador PDO que permite conectar PHP con MariaDB.
RUN docker-php-ext-install pdo_mysql
WORKDIR /var/www/html
# La imagen lleva el código; el volumen de Compose permite editarlo en vivo.
COPY src/ /var/www/html/
