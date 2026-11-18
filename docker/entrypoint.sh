#!/bin/bash
# =============================================================
#  Planazo — Script de arranque del contenedor Laravel
#  Se ejecuta automáticamente al iniciar el contenedor "app"
# =============================================================
set -e

cd /var/www/html

echo ""
echo "╔══════════════════════════════════════╗"
echo "║        🚀  Arrancando Planazo        ║"
echo "╚══════════════════════════════════════╝"
echo ""

# ── 1. Crear o ajustar .env ───────────────────────────────────
if [ ! -f .env ]; then
    echo "📄  Creando .env desde .env.example..."
    cp .env.example .env
fi

# Detectar motor de base de datos
DB_CONN="${DB_CONNECTION:-mysql}"

if [ "$DB_CONN" = "sqlite" ] || [ -z "$DB_HOST" ]; then
    echo "💾  Configurando SQLite para almacenamiento local..."
    mkdir -p database
    touch database/database.sqlite
    chmod 666 database/database.sqlite || true
    sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
    sed -i 's|^DB_DATABASE=.*|DB_DATABASE=/var/www/html/database/database.sqlite|' .env
else
    # Garantizar parámetros de conexión MySQL
    sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
    sed -i "s/^DB_HOST=.*/DB_HOST=${DB_HOST:-mysql}/"             .env
    sed -i "s/^DB_PORT=.*/DB_PORT=${DB_PORT:-3306}/"             .env
    sed -i "s/^# DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE:-proyecto2ihc}/" .env
    sed -i "s/^DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE:-proyecto2ihc}/"   .env
    sed -i "s/^DB_USERNAME=.*/DB_USERNAME=${DB_USERNAME:-root}/"     .env
    sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD:-root}/"     .env
fi

if [ -n "$APP_URL" ]; then
    sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
fi

# ── 2. Generar clave de aplicación si está vacía ──────────────
if grep -qE "^APP_KEY=$" .env || [ -z "$(grep -E "^APP_KEY=" .env | cut -d '=' -f2)" ]; then
    echo "🔑  Generando APP_KEY..."
    php artisan key:generate --force
fi

# ── 3. Esperar a la base de datos si es MySQL ─────────────────
if [ "$DB_CONN" = "mysql" ] && [ -n "$DB_HOST" ]; then
    echo "⏳  Esperando a MySQL en ${DB_HOST}:${DB_PORT:-3306}..."
    MYSQL_HOST="${DB_HOST:-mysql}"
    MYSQL_PORT="${DB_PORT:-3306}"
    MYSQL_DB="${DB_DATABASE:-proyecto2ihc}"
    MYSQL_USER="${DB_USERNAME:-root}"
    MYSQL_PASS="${DB_PASSWORD:-root}"

    until php -r "
        try {
            new PDO('mysql:host=${MYSQL_HOST};port=${MYSQL_PORT};dbname=${MYSQL_DB}', '${MYSQL_USER}', '${MYSQL_PASS}');
            echo 'ok';
        } catch (Exception \$e) {
            exit(1);
        }
    " 2>/dev/null | grep -q "ok"; do
        echo "   MySQL no está listo aún, reintentando en 3s..."
        sleep 3
    done
    echo "✅  MySQL listo."
fi

# ── 4. Ejecutar migraciones y seeders ─────────────────────────
echo "📦  Ejecutando migraciones..."
php artisan migrate --force

echo "🌱  Cargando usuarios y datos de prueba (seeders)..."
php artisan db:seed --force

# ── 4.1 Enlace simbólico de storage ──────────────────────────
php artisan storage:link --force 2>/dev/null || true

# ── 5. Limpiar caché de configuración ────────────────────────
php artisan config:clear
php artisan cache:clear

SERVER_PORT="${PORT:-8000}"

echo ""
echo "╔══════════════════════════════════════════════════╗"
echo "║  ✅  Planazo corriendo en el puerto ${SERVER_PORT}        ║"
echo "╚══════════════════════════════════════════════════╝"
echo ""

# ── 6. Levantar el servidor Laravel ──────────────────────────
exec php artisan serve --host=0.0.0.0 --port="${SERVER_PORT}"
