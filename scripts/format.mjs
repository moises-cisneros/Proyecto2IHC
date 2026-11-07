#!/usr/bin/env node
import { execSync } from 'node:child_process';
import process from 'node:process';

const isFix = process.argv.includes('--fix');
const isCheck = !isFix;

console.log(`🧹 [format] Ejecutando formateador del proyecto (${isFix ? 'Auto-fix' : 'Check'})...`);

let hasError = false;

// 1. Formateador PHP con Laravel Pint
console.log('🐘 1. Verificando código PHP con Laravel Pint...');
try {
    const pintCmd = isFix ? 'php vendor/bin/pint' : 'php vendor/bin/pint --test';
    execSync(pintCmd, { stdio: 'inherit' });
    console.log('✅ PHP (Pint): Formato correcto.');
} catch (error) {
    console.error('❌ PHP (Pint): Se detectaron archivos con problemas de formato.');
    hasError = true;
}

// 2. Formateador Frontend con Prettier (excluyendo markdown por .prettierignore)
console.log('🎨 2. Verificando Frontend (JS, TS, CSS, JSON) con Prettier (excluyendo *.md)...');
try {
    const prettierCmd = isFix
        ? 'npx prettier --write "**/*.{js,mjs,cjs,ts,css,json}"'
        : 'npx prettier --check "**/*.{js,mjs,cjs,ts,css,json}"';
    execSync(prettierCmd, { stdio: 'inherit' });
    console.log('✅ Frontend (Prettier): Formato correcto.');
} catch (error) {
    console.error('❌ Frontend (Prettier): Se detectaron archivos con problemas de formato.');
    hasError = true;
}

if (hasError) {
    console.error('\n⚠️ Hubo discrepancias de formato. Ejecuta "pnpm run format" para solucionarlas automáticamente.');
    process.exit(1);
}

console.log('\n🎉 [format] Todos los archivos del repositorio cumplen el estándar de formato.');
process.exit(0);
