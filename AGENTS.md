# Reglas Operativas para Agentes - Planazo

Este archivo define las directivas y restricciones obligatorias para cualquier agente de inteligencia artificial (Gentle-AI, Antigravity, Claude, OpenCode, Cursor, etc.) que opere en este repositorio. Debe mantenerse alineado con [CONTRIBUTING.md](CONTRIBUTING.md).

---

## 1. Entorno de Ejecución (Docker por Defecto y Modo Manual)

Por defecto, el entorno de desarrollo y evaluación se ejecuta mediante **Docker**, garantizando un entorno reproducible:

```bash
# Iniciar entorno (App: 9300 | Vite: 9373 | MySQL: 9306)
docker compose up --build

# Detener entorno
docker compose down

# Migraciones y seeders en contenedor
docker compose exec planazo_app php artisan migrate
docker compose exec planazo_app php artisan db:seed
```

*(Modo manual alternativo en host requiere PHP 8.2+, Composer 2.x, MySQL 8.0 y Node.js 18+; ver comandos en [CONTRIBUTING.md](CONTRIBUTING.md)).*

---

## 2. Restricciones Académicas y Stack Tecnológico Inviolable

> [!IMPORTANT]
> **REGLA ACADÉMICA ESTRICTA:** Por directiva docente, **NO se puede cambiar la tecnología base del proyecto**.
>
> * **Backend:** Laravel 12 (PHP 8.2+)
> * **Base de Datos:** MySQL 8.0 (persistencia con volumen `mysql_data` en Docker)
> * **Frontend:** Blade Templates modulares + Tailwind CSS 4 + Vite (sin React/Vue/Livewire/SPA externas)
> * **Testing:** PHPUnit (`php artisan test`) y Playwright E2E (`pnpm run test:e2e`)
>
> Prohibido sustituir el stack por Node/Express/Prisma/Next.js. Se permite refactorizar código existente respetando la arquitectura monolítica de Laravel (`app/`, `resources/views/`, `routes/`, `database/`).

---

## 3. Metodología de Desarrollo: ODD (Por Defecto) y OpenSpec

* **Flujo ODD (Organic-Driven Development) con Gentle-AI v4+:**
  * Metodología cotidiana por defecto: ágil, adaptativa y orientada a TDD estricto (*RED &rarr; GREEN &rarr; REFACTOR*).
  * Se apoya en memoria persistente (**Engram**) para contextualizar decisiones y tracking modular en `odd/tasks/` sin burocracia documental innecesaria.
* **Flujo Formal con OpenSpec (Comandos `/opsx`):**
  * Reservado para entregas formales que requieran especificación versionada en `openspec/changes/<cambio>/`. Válido solo si `openspec validate <cambio>` pasa.

---

## 4. Memoria y Contexto: Engram y CodeGraph

* **Engram (Memoria Persistente):**
  * Buscar antes de deducir: `mem_search` con términos clave.
  * Registrar decisiones de arquitectura, bugs resueltos, convenciones y hallazgos con `mem_save` (Qué, Por qué, Dónde, Aprendizaje).
  * Cerrar sesiones con `mem_session_summary`.
* **CodeGraph (Índice Semántico):**
  * Para preguntas estructurales (arquitectura, impacto, referencias), usar `codegraph_explore` antes de exploraciones amplias de sistema de archivos. Comandos de solo lectura permitidos: `status`, `query`, `explore`, `node`, `files`, `callers`, `callees`, `impact`, `affected`.
  * **Prohibido:** `codegraph uninit`, `install`, `uninstall` o `upgrade`.

---

## 5. Reglas Operativas Críticas y No Negociables

1. **CERO ATRIBUCIÓN DE IA (Estricto):**
   * Prohibido mencionar o atribuir autoría a cualquier IA (ni a ti mismo) en commits, PRs, issues, comentarios, código o documentación.
   * Prohibido `Co-Authored-By: ...`, "Generated with ...", o similares.
2. **DESTINO ESTRICTO DE PULL REQUESTS E ISSUES (Fork Independiente):**
   * Este repositorio es un fork independiente (`moises-cisneros/Proyecto2IHC`). **Queda terminantemente prohibido crear PRs o Issues hacia el upstream original.**
   * Comandos de GitHub CLI (`gh`) deben incluir siempre `-R moises-cisneros/Proyecto2IHC --base main`:

     ```bash
     gh pr create -R moises-cisneros/Proyecto2IHC --base main --head <rama> --title "<titulo>" --body "<cuerpo>"
     gh issue create -R moises-cisneros/Proyecto2IHC --title "<titulo>" --body "<cuerpo>"
     ```

3. **SEGURIDAD OBLIGATORIA Y PRINCIPIO DE MENOR PRIVILEGIO:**
   * **Autenticación:** Rutas privadas bajo middleware `auth`. Acceso anónimo debe redirigir (`302`) a login.
   * **Autorización:** Todo controlador debe invocar `Gate::authorize()` o `$this->authorize()` validando con Policies (`app/Policies/`).
   * **Aislamiento Multiusuario:** Filtrar consultas estrictamente por membresía/autoría para evitar fugas de información (*IDOR*).
   * **Bloqueo por Estado:** Recursos en estados cerrados/cancelados bloquean mutaciones (`403 Forbidden`).
   * **Vistas Blade:** Controles de acción protegidos con `@can`.
   * **Tests Negativos Obligatorios:** Toda suite de backend/controlador debe incluir mínimo un caso `302` y un caso `403 Forbidden`.
4. **DESCOMPOSICIÓN DE REQUISITOS DOCENTES (Sub-issues):**
   * El issue madre del docente **nunca** se cierra con `Closes`; se referencia con `Refs #<padre>`.
   * Todo requisito debe dividirse en **mínimo 3 sub-issues atómicos** reflejando las capas:
     1. `backend` (persistencia, migraciones, modelos, reglas de dominio)
     2. `controlador` (endpoints, validación, Form Requests, queries HTTP)
     3. `interfaz` (vistas Blade, componentes UI, CSS Tailwind, accesibilidad)
   * Vincular cada sub-issue al árbol nativo de GitHub:

     ```bash
     gh api -X POST /repos/:owner/:repo/issues/:parent/sub_issues -F sub_issue_id=:id
     ```

   * Cerrar cada sub-issue individualmente con su PR (`Closes #<sub-issue>`).
5. **TAXONOMÍA DE ETIQUETAS (Labels):**
   * Madre: `requisito` (+ `refactor` o `seguridad` si aplica).
   * Sub-issues / PRs: `sub-issue` + etiqueta de capa (`backend`, `controlador`, `interfaz`, o `testing` + `documentation`) + transversales (`seguridad`, `ihc`, `accessibility`).
6. **CONVENCIONES DE IDIOMA:**
   * Código, identificadores y comentarios en **inglés**.
   * Textos de interfaz y documentación en **español**.
   * Conventional Commits (`feat:`, `fix:`, `refactor:`, `test:`, `docs:`, `chore:`).

---

## 6. Skills Canónicas Especializadas (Activación Modular)

Para optimizar el uso de tokens y mantener el contexto ligero, los protocolos extensos y matrices de evidencia están encapsulados en skills versionadas en `.agents/skills/`. **El agente debe leer la skill correspondiente únicamente cuando la tarea lo amerite:**

| Skill | Cuándo Consultarla | Protocolo y Referencia |
| :--- | :--- | :--- |
| **`e2e-testing`** | Al diseñar o ejecutar pruebas E2E con Playwright. | Selectores semánticos accesibles (`getByRole`, `getByLabel`), aislamiento secuencial y capturas en `docs/evidencias/`. |
| **`information-architecture`** | Al estructurar navegación, listados o diagramar flujos. | Taxonomías LATCH, chunking (7 ± 2), rótulos neutros, modelado de Task Flows vs App Maps y doble carril de roles. |
| **`interaction-decisions`** | Al auditar o justificar decisiones ergonómicas de UI. | Ley de Fitts: $T = a + b \log_{2}(2D/W)$, Ley de Hick: $T = b \log_{2}(n + 1)$, Carga Cognitiva y contraste Antes/Después. |
| **`heuristic-evaluation`** | Al ejecutar evaluaciones de usabilidad. | Escala de severidad 0-4 de Nielsen (H1–H10), issue tracker único y sub-issues de hallazgos. |
| **`refactoring`** | Al simplificar controladores o vistas monolíticas. | Transformaciones Fowler seguras, preservación de tests y desacople de lógica sin alterar contratos observables. |

---

## 7. Comandos Rápidos de Verificación

* **Tests Backend (PHPUnit — Mínimo 4 tests por suite):**

  ```bash
  docker compose exec planazo_app php artisan test
  # O archivo puntual:
  docker compose exec planazo_app php artisan test tests/Feature/<Test>.php
  ```

* **Tests E2E (Playwright — Tareas Humanas):**

  ```bash
  pnpm run test:e2e
  pnpm run test:e2e:headed
  ```

* **Verificación de Sub-issues y Ausencia de IA:**

  ```bash
  node scripts/verify-subissue.mjs
  ```
