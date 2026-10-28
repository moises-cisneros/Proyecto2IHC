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

# ── 1. Crear .env si no existe ────────────────────────────────
if [ ! -f .env ]; then
    echo "📄  Creando .env desde .env.example..."
    cp .env.example .env

    # Ajustar para Docker (DB_HOST apunta al servicio "mysql")
    sed -i 's/^DB_HOST=.*/DB_HOST=mysql/'     .env
    sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=root/' .env
    sed -i 's/^DB_USERNAME=.*/DB_USERNAME=root/' .env
fi

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

# ── 4. Ejecutar migraciones ───────────────────────────────────
echo "📦  Ejecutando migraciones..."
php artisan migrate --force

# ── 5. Limpiar caché de configuración ────────────────────────
php artisan config:clear
php artisan cache:clear

echo ""
echo "╔══════════════════════════════════════════════════╗"
echo "║  ✅  Planazo corriendo en http://localhost:8000  ║"
echo "║  🎨  Vite HMR en       http://localhost:5173     ║"
echo "╚══════════════════════════════════════════════════╝"
echo ""

# ── 6. Levantar el servidor Laravel ──────────────────────────
exec php artisan serve --host=0.0.0.0 --port=8000
