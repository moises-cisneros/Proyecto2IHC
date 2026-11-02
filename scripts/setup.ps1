# Setup Script para Planazo (Proyecto 2 IHC)
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "  Verificando Requisitos del Repositorio Planazo" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

$hasErrors = $false

# 1. Git
$gitCmd = Get-Command git -ErrorAction SilentlyContinue
if ($gitCmd) {
    $gitVer = & git --version
    Write-Host "[OK] Git detectado: $gitVer" -ForegroundColor Green
} else {
    Write-Host "[ERROR] Git no esta instalado o no esta en el PATH del sistema." -ForegroundColor Red
    Write-Host "        Descargalo e instalalo desde: https://git-scm.com/" -ForegroundColor Yellow
    $hasErrors = $true
}

# 2. Node.js & npm
$nodeCmd = Get-Command node -ErrorAction SilentlyContinue
if ($nodeCmd) {
    $nodeVer = & node -v
    Write-Host "[OK] Node.js detectado: $nodeVer" -ForegroundColor Green
} else {
    Write-Host "[ERROR] Node.js no esta instalado." -ForegroundColor Red
    Write-Host "        Descargalo desde: https://nodejs.org/" -ForegroundColor Yellow
    $hasErrors = $true
}

$npmCmd = Get-Command npm -ErrorAction SilentlyContinue
if ($npmCmd) {
    $npmVer = & npm -v
    Write-Host "[OK] npm detectado: v$npmVer" -ForegroundColor Green
} else {
    Write-Host "[ERROR] npm no esta disponible en el entorno." -ForegroundColor Red
    $hasErrors = $true
}

# 3. pnpm
$pnpmCmd = Get-Command pnpm -ErrorAction SilentlyContinue
if ($pnpmCmd) {
    $pnpmVer = & pnpm -v
    Write-Host "[OK] pnpm detectado: v$pnpmVer" -ForegroundColor Green
} else {
    Write-Host "[AVISO] pnpm no esta instalado globalmente." -ForegroundColor Yellow
    if ($npmCmd) {
        Write-Host "[INFO] Instalando pnpm mediante 'npm install -g pnpm'..." -ForegroundColor Cyan
        & npm install -g pnpm
        $pnpmCmdAfter = Get-Command pnpm -ErrorAction SilentlyContinue
        if ($pnpmCmdAfter) {
            $pnpmVerAfter = & pnpm -v
            Write-Host "[OK] pnpm instalado correctamente: v$pnpmVerAfter" -ForegroundColor Green
        } else {
            Write-Host "[AVISO] pnpm fue instalado con npm, pero podria requerir reiniciar la terminal." -ForegroundColor Yellow
        }
    } else {
        Write-Host "[ERROR] Se requiere pnpm. Instalalo ejecutando: npm install -g pnpm" -ForegroundColor Red
        $hasErrors = $true
    }
}

# 4. Docker CLI
$dockerCmd = Get-Command docker -ErrorAction SilentlyContinue
if ($dockerCmd) {
    $dockerVer = & docker --version
    Write-Host "[OK] Docker detectado: $dockerVer" -ForegroundColor Green
} else {
    Write-Host "[ERROR] Docker no esta instalado en el sistema." -ForegroundColor Red
    Write-Host "        Instala Docker Desktop desde: https://www.docker.com/products/docker-desktop" -ForegroundColor Yellow
    $hasErrors = $true
}

# 5. Docker Compose
if ($dockerCmd) {
    $composeOutput = & docker compose version 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "[OK] Docker Compose detectado: $composeOutput" -ForegroundColor Green
    } else {
        Write-Host "[ERROR] Docker Compose no esta disponible." -ForegroundColor Red
        $hasErrors = $true
    }
}

# 6. Docker Engine / Daemon
if ($dockerCmd) {
    $null = & docker info 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "[OK] Docker Engine esta en ejecucion activa." -ForegroundColor Green
    } else {
        Write-Host "[AVISO] Docker esta instalado pero Docker Desktop no parece estar ejecutandose." -ForegroundColor Yellow
        Write-Host "        Inicia Docker Desktop antes de levantar los contenedores." -ForegroundColor Yellow
    }
}

# 7. Archivo .env
if (Test-Path ".env") {
    Write-Host "[OK] Archivo .env presente en la raiz del proyecto." -ForegroundColor Green
} elseif (Test-Path ".env.example") {
    Write-Host "[INFO] Creando archivo .env a partir de .env.example..." -ForegroundColor Cyan
    Copy-Item ".env.example" ".env"
    Write-Host "[OK] Archivo .env generado exitosamente." -ForegroundColor Green
} else {
    Write-Host "[AVISO] No se encontro .env ni .env.example en la raiz." -ForegroundColor Yellow
}

# 8. Herramientas opcionales de Host
Write-Host ""
Write-Host "--- Herramientas para Modo Manual Host (Opcionales si usas Docker) ---" -ForegroundColor DarkGray
$phpCmd = Get-Command php -ErrorAction SilentlyContinue
if ($phpCmd) {
    $phpVer = (& php -v)[0]
    Write-Host "[INFO] PHP en host: $phpVer" -ForegroundColor DarkCyan
} else {
    Write-Host "[INFO] PHP en host: No instalado (No requerido si ejecutas con Docker)" -ForegroundColor DarkGray
}

$composerCmd = Get-Command composer -ErrorAction SilentlyContinue
if ($composerCmd) {
    $composerVer = (& composer --version)[0]
    Write-Host "[INFO] Composer en host: $composerVer" -ForegroundColor DarkCyan
} else {
    Write-Host "[INFO] Composer en host: No instalado (No requerido si ejecutas con Docker)" -ForegroundColor DarkGray
}

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
if ($hasErrors) {
    Write-Host "[ERROR] Faltan herramientas esenciales para el inicio del proyecto." -ForegroundColor Red
    Write-Host "        Revisa los mensajes anteriores e instala las herramientas faltantes." -ForegroundColor Red
    Write-Host ""
    exit 1
} else {
    Write-Host "[EXITO] Todas las herramientas base del repositorio estan listas!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Comandos para iniciar el proyecto:" -ForegroundColor Cyan
    Write-Host "  - Con Docker (Recomendado): docker compose up --build" -ForegroundColor White
    Write-Host "  - Con Host manual: composer install; pnpm install; php artisan serve" -ForegroundColor White
    Write-Host ""
    exit 0
}
