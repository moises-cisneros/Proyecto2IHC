# ============================================================
#  Planazo — Imagen PHP para desarrollo con Docker
# ============================================================
FROM php:8.2-cli

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Extensiones PHP necesarias para Laravel + MySQL
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Instalar Composer desde su imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar dependencias primero (mejor uso del cache de capas)
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --no-interaction

# Copiar el resto del proyecto
COPY . .

# Finalizar autoloader de Composer
RUN composer dump-autoload --optimize

# Permisos de escritura para Laravel
RUN chmod -R 775 storage bootstrap/cache

# Exponer puerto del servidor de desarrollo de Laravel
EXPOSE 8000

# Script de arranque
ENTRYPOINT ["/bin/bash", "/var/www/html/docker/entrypoint.sh"]
