# Usamos una imagen base de PHP con Apache
FROM php:8.1-apache

# 1. Instalar dependencias del sistema necesarias para MongoDB
RUN apt-get update && apt-get install -y \
    libssl-dev \
    pkg-config \
    zlib1g-dev \
    libpng-dev \
    git \
    unzip \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# 2. Activar mod_rewrite de Apache (para URLs amigables si las usas)
RUN a2enmod rewrite

# 3. Copiar los archivos de tu proyecto al servidor
COPY . /var/www/html/

# 4. Instalar Composer (Gestor de paquetes PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Ejecutar la instalación de dependencias (instala la librería de MongoDB)
RUN composer install --no-dev --optimize-autoloader

# 6. Ajustar permisos para que Apache pueda leer los archivos
RUN chown -R www-data:www-data /var/www/html/

# Exponer el puerto 80
EXPOSE 80