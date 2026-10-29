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

# ── 1. Crear o ajustar .env para Docker ───────────────────────
if [ ! -f .env ]; then
    echo "📄  Creando .env desde .env.example..."
    cp .env.example .env
fi

# Garantizar parámetros de conexión Docker en .env
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
sed -i 's/^DB_HOST=.*/DB_HOST=mysql/'             .env
sed -i 's/^DB_PORT=.*/DB_PORT=3306/'             .env
sed -i 's/^# DB_DATABASE=.*/DB_DATABASE=proyecto2ihc/' .env
sed -i 's/^DB_DATABASE=.*/DB_DATABASE=proyecto2ihc/'   .env
sed -i 's/^DB_USERNAME=.*/DB_USERNAME=root/'     .env
sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=root/'     .env
sed -i 's|^APP_URL=.*|APP_URL=http://localhost:9300|' .env

# ── 2. Generar clave de aplicación si está vacía ──────────────
if grep -qE "^APP_KEY=$" .env; then
    echo "🔑  Generando APP_KEY..."
    php artisan key:generate --force
fi

# ── 3. Esperar a que MySQL esté listo ─────────────────────────
echo "⏳  Esperando a MySQL..."
until php -r "
    try {
        new PDO('mysql:host=mysql;dbname=proyecto2ihc', 'root', 'root');
        echo 'ok';
    } catch (Exception \$e) {
        exit(1);
    }
" 2>/dev/null | grep -q "ok"; do
    echo "   MySQL no está listo aún, reintentando en 3s..."
    sleep 3
done
echo "✅  MySQL listo."

# ── 4. Ejecutar migraciones y seeders ─────────────────────────
echo "📦  Ejecutando migraciones..."
php artisan migrate --force

echo "🌱  Cargando usuarios y datos de prueba (seeders)..."
php artisan db:seed --force

# ── 5. Limpiar caché de configuración ────────────────────────
php artisan config:clear
php artisan cache:clear

echo ""
echo "╔══════════════════════════════════════════════════╗"
echo "║  ✅  Planazo corriendo en http://localhost:9300  ║"
echo "║  🎨  Vite HMR en       http://localhost:9373     ║"
echo "╚══════════════════════════════════════════════════╝"
echo ""

# ── 6. Levantar el servidor Laravel ──────────────────────────
exec php artisan serve --host=0.0.0.0 --port=8000
