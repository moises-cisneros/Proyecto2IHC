# ============================================================
#  Planazo — Dockerfile Multi-stage para Producción y Desarrollo
# ============================================================

# ── Stage 1: Build de Assets Frontend con Node 22 / pnpm ────
FROM node:22-alpine AS frontend

WORKDIR /app

RUN npm install -g pnpm@10

COPY package.json pnpm-lock.yaml ./
RUN pnpm install --frozen-lockfile

COPY . .
RUN pnpm run build

# ── Stage 2: Servidor PHP 8.2 ───────────────────────────────
FROM php:8.2-cli

# Dependencias del sistema (soporte para MySQL y SQLite)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    sqlite3 \
    libsqlite3-dev \
    zip \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Extensiones PHP necesarias para Laravel + MySQL + SQLite
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd

# Instalar Composer desde su imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar dependencias de Composer primero (mejor uso del cache de capas)
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --no-interaction

# Copiar el resto del proyecto
COPY . .

# Copiar assets compilados desde el stage frontend
COPY --from=frontend /app/public/build /var/www/html/public/build

# Finalizar autoloader de Composer
RUN composer dump-autoload --optimize

# Permisos de escritura para Laravel
RUN chmod -R 775 storage bootstrap/cache

# Exponer puertos (8000 para local, 10000 para Render)
EXPOSE 8000 10000

# Script de arranque
ENTRYPOINT ["/bin/bash", "/var/www/html/docker/entrypoint.sh"]
