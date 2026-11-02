#!/usr/bin/env bash
set -e

echo "============================================================"
echo "  🔍 Verificando Requisitos del Repositorio Planazo"
echo "============================================================"
echo ""

HAS_ERRORS=0

# 1. Git
if command -v git &> /dev/null; then
    echo "✅ Git detectado: $(git --version)"
else
    echo "❌ Git no está instalado o no está en el PATH."
    echo "   Instálalo desde: https://git-scm.com/"
    HAS_ERRORS=1
fi

# 2. Node.js & npm
if command -v node &> /dev/null; then
    NODE_VER=$(node -v)
    echo "✅ Node.js detectado: $NODE_VER"
else
    echo "❌ Node.js no está instalado."
    echo "   Instálalo desde: https://nodejs.org/"
    HAS_ERRORS=1
fi

if command -v npm &> /dev/null; then
    echo "✅ npm detectado: v$(npm -v)"
else
    echo "❌ npm no está disponible en el entorno."
    HAS_ERRORS=1
fi

# 3. pnpm
if command -v pnpm &> /dev/null; then
    echo "✅ pnpm detectado: v$(pnpm -v)"
else
    echo "⚠️  pnpm no está instalado globalmente."
    if command -v npm &> /dev/null; then
        echo "⏳ Instalando pnpm mediante 'npm install -g pnpm'..."
        npm install -g pnpm || true
        if command -v pnpm &> /dev/null; then
            echo "✅ pnpm instalado correctamente: v$(pnpm -v)"
        else
            echo "⚠️  pnpm fue instalado con npm, pero podría requerir reiniciar la terminal."
        fi
    else
        echo "❌ Se requiere pnpm. Instálalo ejecutando: npm install -g pnpm"
        HAS_ERRORS=1
    fi
fi

# 4. Docker CLI
if command -v docker &> /dev/null; then
    echo "✅ Docker detectado: $(docker --version)"
else
    echo "❌ Docker no está instalado en el sistema."
    echo "   Instala Docker Desktop desde: https://www.docker.com/products/docker-desktop"
    HAS_ERRORS=1
fi

# 5. Docker Compose
if docker compose version &> /dev/null; then
    echo "✅ Docker Compose detectado: $(docker compose version)"
else
    echo "❌ Docker Compose no está disponible."
    HAS_ERRORS=1
fi

# 6. Docker Engine / Daemon
if command -v docker &> /dev/null; then
    if docker info &> /dev/null; then
        echo "✅ Docker Engine está en ejecución activa."
    else
        echo "⚠️  Docker está instalado pero el demonio no parece estar en ejecución."
        echo "   Inicia Docker Desktop antes de levantar los contenedores."
    fi
fi

# 7. Archivo .env
if [ -f ".env" ]; then
    echo "✅ Archivo .env presente en la raíz del proyecto."
elif [ -f ".env.example" ]; then
    echo "⏳ Creando archivo .env a partir de .env.example..."
    cp .env.example .env
    echo "✅ Archivo .env generado exitosamente."
else
    echo "⚠️  No se encontró .env ni .env.example en la raíz."
fi

# 8. Host tools opcionales
echo ""
echo "--- Herramientas para Modo Manual Host (Opcionales si usas Docker) ---"
if command -v php &> /dev/null; then
    echo "ℹ️  PHP en host: $(php -v | head -n 1)"
else
    echo "ℹ️  PHP en host: No instalado (No requerido si ejecutas con Docker)"
fi

if command -v composer &> /dev/null; then
    echo "ℹ️  Composer en host: $(composer --version | head -n 1)"
else
    echo "ℹ️  Composer en host: No instalado (No requerido si ejecutas con Docker)"
fi

echo ""
echo "============================================================"
if [ $HAS_ERRORS -ne 0 ]; then
    echo "❌ Faltan herramientas esenciales para el inicio del proyecto."
    echo "   Revisa los mensajes anteriores e instala las herramientas faltantes."
    echo ""
    exit 1
else
    echo "🎉 ¡Todas las herramientas base del repositorio están listas!"
    echo ""
    echo "Comandos para iniciar el proyecto:"
    echo "  • Con Docker (Recomendado): docker compose up --build"
    echo "  • Con Host manual: composer install && pnpm install && php artisan serve"
    echo ""
    exit 0
fi

