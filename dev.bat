@echo off
setlocal
echo ============================================================
echo   Iniciando Proyecto 2 IHC (Planazo) con Docker
echo ============================================================
echo.

where docker >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [ERROR] Docker no esta instalado en este sistema.
    echo Por favor instala Docker Desktop desde: https://www.docker.com/products/docker-desktop
    echo.
    pause
    exit /b 1
)

docker info >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [ERROR] Docker Desktop no se esta ejecutando.
    echo Por favor inicia Docker Desktop e intenta nuevamente.
    echo.
    pause
    exit /b 1
)

echo [*] Levantando contenedores (App Laravel + MySQL + Vite)...
echo.
docker compose up --build

endlocal
