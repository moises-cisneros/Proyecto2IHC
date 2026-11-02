#!/usr/bin/env node
import { execSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';

function run(cmd, options = {}) {
  try {
    const output = execSync(cmd, {
      encoding: 'utf-8',
      stdio: ['pipe', 'pipe', 'pipe'],
      shell: true,
      ...options,
    });
    return output ? output.trim() : '';
  } catch (err) {
    if (options.allowFailure) {
      return null;
    }
    throw err;
  }
}

console.log('============================================================');
console.log('  🔍 Verificando Requisitos del Repositorio Planazo');
console.log('============================================================\n');

let hasErrors = false;

// 1. Git
const gitVer = run('git --version', { allowFailure: true });
if (gitVer) {
  console.log(`✅ Git detectado: ${gitVer}`);
} else {
  console.error('❌ Git no está instalado o no está en el PATH del sistema.');
  console.error('   Descárgalo e instálalo desde: https://git-scm.com/');
  hasErrors = true;
}

// 2. Node.js & npm
const nodeVer = process.version;
const majorNode = parseInt(nodeVer.replace('v', '').split('.')[0], 10);
if (majorNode >= 18) {
  console.log(`✅ Node.js detectado: ${nodeVer} (>= 18 requerido)`);
} else {
  console.error(`❌ Versión de Node.js insuficiente (${nodeVer}). Se requiere Node.js 18+`);
  console.error('   Descárgalo desde: https://nodejs.org/');
  hasErrors = true;
}

const npmVer = run('npm --version', { allowFailure: true });
if (npmVer) {
  console.log(`✅ npm detectado: v${npmVer}`);
} else {
  console.error('❌ npm no está disponible en el entorno.');
  hasErrors = true;
}

// 3. pnpm
let pnpmVer = run('pnpm --version', { allowFailure: true });
if (pnpmVer) {
  console.log(`✅ pnpm detectado: v${pnpmVer}`);
} else {
  console.log('⚠️  pnpm no está instalado globalmente.');
  if (npmVer) {
    console.log('⏳ Instalando pnpm mediante npm install -g pnpm...');
    try {
      execSync('npm install -g pnpm', { stdio: 'inherit' });
      pnpmVer = run('pnpm --version', { allowFailure: true });
      if (pnpmVer) {
        console.log(`✅ pnpm instalado correctamente: v${pnpmVer}`);
      } else {
        console.warn('⚠️  pnpm fue instalado pero aún no se detecta en la sesión actual. Reinicia la terminal si es necesario.');
      }
    } catch (e) {
      console.error('❌ No se pudo instalar pnpm automáticamente.');
      console.error('   Ejecuta manualmente: npm install -g pnpm');
      hasErrors = true;
    }
  } else {
    console.error('❌ Se requiere pnpm. Instálalo ejecutando: npm install -g pnpm');
    hasErrors = true;
  }
}

// 4. Docker CLI
const dockerVer = run('docker --version', { allowFailure: true });
if (dockerVer) {
  console.log(`✅ Docker detectado: ${dockerVer}`);
} else {
  console.error('❌ Docker no está instalado en el sistema.');
  console.error('   Instala Docker Desktop desde: https://www.docker.com/products/docker-desktop');
  hasErrors = true;
}

// 5. Docker Compose
const composeVer = run('docker compose version', { allowFailure: true });
if (composeVer) {
  console.log(`✅ Docker Compose detectado: ${composeVer}`);
} else {
  console.error('❌ Docker Compose no está disponible.');
  hasErrors = true;
}

// 6. Docker Daemon / Engine
if (dockerVer) {
  const dockerInfo = run('docker info', { allowFailure: true });
  if (dockerInfo) {
    console.log('✅ Docker Engine está en ejecución activa.');
  } else {
    console.warn('⚠️  Docker está instalado pero el demonio (Docker Desktop) no parece estar en ejecución.');
    console.warn('   Por favor inicia Docker Desktop antes de levantar los contenedores.');
  }
}

// 7. Configuración .env
const rootDir = process.cwd();
const envPath = path.join(rootDir, '.env');
const envExamplePath = path.join(rootDir, '.env.example');

if (fs.existsSync(envPath)) {
  console.log('✅ Archivo .env presente en la raíz del proyecto.');
} else if (fs.existsSync(envExamplePath)) {
  console.log('⏳ Creando archivo .env a partir de .env.example...');
  fs.copyFileSync(envExamplePath, envPath);
  console.log('✅ Archivo .env generado exitosamente.');
} else {
  console.warn('⚠️  No se encontró .env ni .env.example en la raíz.');
}

// 8. Herramientas opcionales para modo manual (Host local)
console.log('\n--- Herramientas para Modo Manual Host (Opcionales si usas Docker) ---');
const phpVer = run('php -v', { allowFailure: true });
if (phpVer) {
  const phpFirstLine = phpVer.split('\n')[0];
  console.log(`ℹ️  PHP en host: ${phpFirstLine}`);
} else {
  console.log('ℹ️  PHP en host: No instalado (No requerido si ejecutas con Docker)');
}

const composerVer = run('composer --version', { allowFailure: true });
if (composerVer) {
  const composerFirstLine = composerVer.split('\n')[0];
  console.log(`ℹ️  Composer en host: ${composerFirstLine}`);
} else {
  console.log('ℹ️  Composer en host: No instalado (No requerido si ejecutas con Docker)');
}

console.log('\n============================================================');
if (hasErrors) {
  console.error('❌ Faltan herramientas esenciales para el inicio del proyecto.');
  console.error('   Revisa los mensajes anteriores e instala las herramientas faltantes.');
  process.exit(1);
} else {
  console.log('🎉 ¡Todas las herramientas base del repositorio están listas!');
  console.log('\nComandos para iniciar el proyecto:');
  console.log('  • Con Docker (Recomendado): docker compose up --build');
  console.log('  • Con Host manual: composer install && pnpm install && php artisan serve\n');
  process.exit(0);
}
