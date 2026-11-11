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

// 2. Formateador Frontend y Markdown con Prettier
console.log('🎨 2. Formateando archivos (JS, TS, CSS, JSON, Markdown) con Prettier...');
try {
    const prettierCmd = isFix
        ? 'npx prettier --write "**/*.{js,mjs,cjs,ts,css,json,md}"'
        : 'npx prettier --check "**/*.{js,mjs,cjs,ts,css,json,md}"';
    execSync(prettierCmd, { stdio: 'inherit' });
    console.log('✅ Prettier (Frontend + Markdown): Formato correcto.');
} catch (error) {
    console.error('❌ Prettier: Se detectaron archivos con problemas de formato.');
    hasError = true;
}

// 3. Linter de Markdown con markdownlint
console.log('📝 3. Verificando consistencia de Markdown con markdownlint (Linter)...');
try {
    const mdCmd = 'npx markdownlint "**/*.md" --ignore-path .markdownlintignore';
    execSync(mdCmd, { stdio: 'inherit' });
    console.log('✅ Markdown (markdownlint): Validación de sintaxis correcta.');
} catch (error) {
    console.error('❌ Markdown (markdownlint): Se detectaron problemas de sintaxis o estructura en archivos Markdown.');
    hasError = true;
}

if (hasError) {
    console.error('\n⚠️ Hubo discrepancias de formato. Ejecuta "pnpm run format" para solucionarlas automáticamente.');
    process.exit(1);
}

console.log('\n🎉 [format] Todos los archivos del repositorio cumplen el estándar de formato.');
process.exit(0);
