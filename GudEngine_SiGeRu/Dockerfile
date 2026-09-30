FROM php:8.1-apache
# Instalar extensiones necesarias de PHP para MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli
# Copiar el código fuente de la aplicación al directorio web de Apache
COPY . /var/www/html/
# En sistemas Linux, Apache ejecuta los procesos PHP bajo el usuario del sistema llamado www-data
RUN chown -R www-data:www-data /var/www/html
#ajustar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html