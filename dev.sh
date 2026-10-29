#!/usr/bin/env bash
set -e

echo "============================================================"
echo "  Iniciando Proyecto 2 IHC (Planazo) con Docker"
echo "============================================================"
echo ""

if ! command -v docker &> /dev/null; then
    echo "[ERROR] Docker no está instalado en este sistema."
    echo "Instala Docker desde: https://docs.docker.com/get-docker/"
    exit 1
fi

if ! docker info > /dev/null 2>&1; then
    echo "[ERROR] El servicio de Docker no está en ejecución."
    echo "Inicia Docker Desktop o el daemon e intenta nuevamente."
    exit 1
fi

echo "[*] Levantando contenedores (App Laravel + MySQL + Vite)..."
echo ""
docker compose up --build
