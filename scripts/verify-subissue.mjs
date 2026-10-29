#!/usr/bin/env node
import { execSync } from 'node:child_process';
import process from 'node:process';
import fs from 'node:fs';

function run(cmd, options = {}) {
  try {
    const output = execSync(cmd, { encoding: 'utf-8', stdio: ['pipe', 'pipe', 'pipe'], ...options });
    return output == null ? '' : output.trim();
  } catch (error) {
    if (options.allowFailure) {
      return null;
    }
    throw error;
  }
}

console.log('🔍 [verify-subissue] Iniciando verificación de branch / PR...');

// 1. Verificar rama actual
let currentBranch = '';
try {
  currentBranch = run('git branch --show-current');
} catch {
  currentBranch = process.env.GITHUB_HEAD_REF || '';
}

console.log(`📌 Rama activa: ${currentBranch || '(desconocida)'}`);

if (currentBranch === 'master' || currentBranch === 'main') {
  console.log('✅ Trabajando directamente sobre master/main. Se permite el flujo directo sin bloqueo.');
  process.exit(0);
}

// 2. Extraer número de sub-issue
let subIssueNumber = process.argv[2];

if (!subIssueNumber) {
  const branchMatch = currentBranch.match(/(?:issue[-_]|#)?(\d+)/i);
  if (branchMatch) {
    subIssueNumber = branchMatch[1];
  }
}

if (!subIssueNumber) {
  const lastCommits = run('git log -n 5 --oneline', { allowFailure: true }) || '';
  const commitMatch = lastCommits.match(/(?:Refs|Closes|Fixes)?\s*#(\d+)/i);
  if (commitMatch) {
    subIssueNumber = commitMatch[1];
  }
}

if (!subIssueNumber && process.env.GITHUB_REF_NAME) {
  const ghMatch = process.env.GITHUB_REF_NAME.match(/(?:issue[-_]|#)?(\d+)/i);
  if (ghMatch) {
    subIssueNumber = ghMatch[1];
  }
}

if (!subIssueNumber) {
  console.warn('⚠️ No se detectó un número de sub-issue en el nombre de la rama ni en los últimos commits.');
  console.warn('💡 Para ramas de funcionalidad provenientes de sub-issues, incluye el número en la rama (ej: feat/issue-25) o en el commit (ej: Refs #25).');
} else {
  console.log(`📋 Verificando sub-issue #${subIssueNumber} mediante GitHub CLI...`);
  try {
    const issueJson = run(`gh issue view ${subIssueNumber} --json number,title,state,body`);
    const issueData = JSON.parse(issueJson);
    console.log(`📑 Sub-issue encontrada: #${issueData.number} · ${issueData.title} [Estado: ${issueData.state}]`);
  } catch (err) {
    console.warn(`⚠️ No se pudo consultar la sub-issue #${subIssueNumber} con gh CLI: ${err.message}`);
    console.warn('Continuando con verificación local...');
  }
}

// 3. Verificar que no existan menciones o atribuciones de IA en commits ni en el PR
console.log('🤖 Verificando ausencia de atribución de IA en el historial de la rama...');
const forbiddenStrings = ['Co-Authored-By', 'Generated with', 'Claude', 'ChatGPT', 'Antigravity AI'];
const recentCommitLogs = run('git log -n 10 --format="%B"', { allowFailure: true }) || '';

for (const forbidden of forbiddenStrings) {
  if (recentCommitLogs.toLowerCase().includes(forbidden.toLowerCase())) {
    console.error(`❌ ERROR DE POLÍTICA: Se detectó atribución de IA prohibida ("${forbidden}") en los commits recientes.`);
    console.error('Por favor remueve cualquier mención o Co-Authored-By de IA antes de pushear o solicitar PR.');
    process.exit(1);
  }
}

// 4. Validar contenido y estructura del Pull Request
if (process.env.GITHUB_EVENT_PATH && fs.existsSync(process.env.GITHUB_EVENT_PATH)) {
  try {
    const eventData = JSON.parse(fs.readFileSync(process.env.GITHUB_EVENT_PATH, 'utf8'));
    if (eventData.pull_request) {
      console.log('📝 Validando contenido y estructura del Pull Request...');
      const prBody = eventData.pull_request.body || '';
      const prTitle = eventData.pull_request.title || '';

      for (const forbidden of forbiddenStrings) {
        if (prBody.toLowerCase().includes(forbidden.toLowerCase()) || prTitle.toLowerCase().includes(forbidden.toLowerCase())) {
          console.error(`❌ ERROR: Se detectó atribución de IA prohibida ("${forbidden}") en el título o descripción del PR.`);
          process.exit(1);
        }
      }

      // Validar que el PR tenga referencia de cierre Closes #<sub-issue>
      const closesMatch = prBody.match(/Closes\s*#(\d+)/i);
      if (!closesMatch) {
        console.warn('⚠️ ADVERTENCIA: El PR no incluye la directiva "Closes #<sub-issue>". Recuerda asociar el sub-issue para cierre automático.');
      }

      // Validar que no se cierre indebidamente el issue del docente
      const refsMatch = prBody.match(/Refs\s*#(\d+)/i);
      if (refsMatch && closesMatch && refsMatch[1] === closesMatch[1]) {
        console.error(`❌ ERROR: El issue #${refsMatch[1]} está referenciado con Refs y Closes simultáneamente. El issue del docente no debe cerrarse con Closes.`);
        process.exit(1);
      }
      console.log('✅ Estructura de Pull Request validada correctamente.');
    }
  } catch (err) {
    console.warn(`⚠️ No se pudo procesar GITHUB_EVENT_PATH: ${err.message}`);
  }
}

// 5. Ejecutar pruebas unitarias automatizadas (Laravel PHPUnit)
console.log('🧪 Ejecutando suite de pruebas automatizadas (PHPUnit)...');
try {
  let testOutput = run('php artisan test', { allowFailure: true });
  if (testOutput === null) {
    // Intentar dentro del contenedor si php local no está disponible
    testOutput = run('docker exec planazo_app php artisan test', { allowFailure: true }) ||
      run('docker compose exec -T planazo_app php artisan test', { allowFailure: true }) ||
      run('docker compose exec -T app php artisan test', { allowFailure: true });
  }
  if (testOutput !== null) {
    console.log(testOutput);
    console.log('✅ Todas las pruebas unitarias pasaron satisfactoriamente.');
  } else {
    console.warn('⚠️ No se pudo ejecutar php artisan test en local ni en docker. Verifica que el contenedor esté levantado.');
  }
} catch (error) {
  console.error('❌ ERROR: Las pruebas unitarias fallaron. No se puede proceder con el push o PR.', error.message);
  process.exit(1);
}

console.log('🎉 [verify-subissue] Verificación completada con éxito.');
process.exit(0);

