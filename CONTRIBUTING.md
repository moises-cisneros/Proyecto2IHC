# Guía de Contribución - Planazo

¡Gracias por tu interés en contribuir a **Planazo**! Este proyecto sigue un flujo de trabajo estructurado y profesional, integrando desarrollo asistido y guiado por especificaciones con **Gentle-AI** y **OpenSpec**.

Para mantener la consistencia técnica y la calidad del código, todos los colaboradores y agentes automatizados deben seguir estas pautas.

---

## 1. Configuración del Entorno de Desarrollo (Setup)

Para empezar a contribuir, necesitás tener el proyecto corriendo localmente. Podés elegir entre el flujo estándar con **Docker** (recomendado) o la **ejecución manual en tu host**.

### Requisitos Previos

* **Con Docker (recomendado):** [Docker Desktop](https://www.docker.com/products/docker-desktop) y Git.
* **Sin Docker (manual):** PHP 8.2+, Composer 2.x, MySQL 8.0, Node.js 18+ y Git.

---

### Paso 1: Clonar el Repositorio

```bash
git clone https://github.com/moises-cisneros/Proyecto2IHC.git
cd Proyecto2IHC
```

---

### Paso 2: Levantar el Entorno

#### Opción A: Con Docker (Estándar recomendado)

El comando estándar para levantar todo el entorno en cualquier terminal y sistema operativo es:

```bash
docker compose up --build
```

*(En Windows, si preferís un atajo por consola o doble clic, podés usar opcionalmente `dev.bat`).*

Esto inicia automáticamente los servicios:

* **App web:** `http://localhost:9300`
* **Vite HMR:** `http://localhost:9373`
* **MySQL 8.0:** `127.0.0.1:9306`

Para detener los contenedores:

```bash
docker compose down
```

*(En Windows también podés usar el atajo `down.bat`).*

---

#### Opción B: Modo Manual (Host Local)

1. Instalar dependencias:

```bash
composer install
npm install
```

1. Configurar el archivo de entorno y generar la clave:

```bash
cp .env.example .env
php artisan key:generate
```

1. Configurar tus credenciales de MySQL en `.env` y ejecutar migraciones:

```bash
php artisan migrate
```

1. Levantar los servidores de desarrollo (en terminales separadas):

```bash
php artisan serve
```

```bash
npm run dev
```

---

### Paso 3: Verificar que Todo Funcione

Ejecutá la suite de pruebas para confirmar que tu entorno está listo para recibir cambios:

Con Docker:

```bash
docker compose exec planazo_app php artisan test
```

En host local:

```bash
php artisan test
```

*(Para ver credenciales de prueba preconfiguradas y puertos de red, consultá el [README.md](README.md)).*

---

## 2. Base de Datos, Migraciones y Seeders

El backend utiliza **Laravel Eloquent ORM** conectado al servicio MySQL `planazo_mysql`.

### Migraciones

Al iniciar los contenedores en Docker, las migraciones se aplican solas. Si creás una nueva migración o necesitás correrlas manualmente:

```bash
docker compose exec planazo_app php artisan migrate
```

### Seeders de Datos

Para poblar la base de datos con registros de prueba:

```bash
docker compose exec planazo_app php artisan db:seed
```

---

## 3. Arquitectura del Proyecto

El repositorio está organizado como una aplicación web monolítica basada en **Laravel 12**:

```text
├── app/                  # Lógica de aplicación (Controllers, Requests, Policies, Models, Providers)
├── bootstrap/            # Inicialización del framework (app.php)
├── config/               # Archivos de configuración de Laravel
├── database/             # Migraciones, factories y seeders
├── docker/               # Scripts de arranque y aprovisionamiento Docker
├── docs/                 # Documentación técnica, evidencias y entregas de clase
├── openspec/             # Especificaciones, propuestas y cambios de OpenSpec
├── public/               # Raíz pública del servidor web (assets, js/ui.js)
├── resources/            # Vistas Blade modulares, componentes, Tailwind v4
├── routes/               # Definición de rutas web y consola
├── scripts/              # Scripts de verificación y automatización
├── tests/                # Pruebas automatizadas (Unit / Feature con PHPUnit)
├── docker-compose.yml    # Orquestación de servicios (planazo_app, planazo_mysql, planazo_vite)
├── Dockerfile            # Imagen base de ejecución PHP/Laravel
├── AGENTS.md             # Instrucciones y restricciones para agentes de IA
├── ARCHITECTURE.md       # Mapa y capas arquitectónicas del sistema
├── CONTRIBUTING.md       # Esta guía
├── DESIGN.md             # Guía oficial del sistema de diseño y tokens
└── README.md             # Documentación e inicio rápido
```

*(Consulta el [README.md](README.md) y los registros de referencia en [docs/](docs/) para conocer las credenciales y detalles).*

---

## 4. Flujo de Trabajo Spec-Driven (Gentle-AI & OpenSpec)

El desarrollo en Planazo sigue la metodología **Spec-Driven Development (SDD)**:

1. **No codificar sin especificación:** Ninguna funcionalidad o cambio de arquitectura se escribe directamente sin antes definir su propuesta y tareas.
2. **Ciclo de OpenSpec (con OpenSpec directo o con Gentle-AI):** ambos caminos son válidos y generan los mismos artefactos en `openspec/changes/<cambio>/`.
   * **Explorar:** Clarificar requerimientos y analizar el estado actual (`/opsx-explore` o `/gentle-sdd-explore`).
   * **Proponer:** Crear la propuesta formal con deltas de especificación, diseño y lista de tareas (`/opsx-propose`, o `/gentle-sdd-new` seguido de `/gentle-sdd-ff`).
   * **Implementar:** Ejecutar las tareas paso a paso (`/opsx-apply` o `/gentle-sdd-apply`, y `/gentle-sdd-verify` para validar).
   * **Sincronizar / Archivar:** Actualizar las especificaciones principales y archivar el cambio completado (`/opsx-sync` y `/opsx-archive`, o `/gentle-sdd-archive`).
   * **Almacén de artefactos:** con Gentle-AI elige **OpenSpec** o **Ambos** (hybrid con Engram); solo Engram no deja archivos en el repo y no es válido. Tus preferencias personales (ritmo, estrategia de PR) no se commitean.
   * **Validación común:** un cambio es válido si `openspec validate <cambio>` pasa, sin importar la herramienta usada.

### Distribución de Skills y Configuración Multi-Agente

* **Directorio compartido canónico (`.agents/skills/`):**
  Es la fuente de la verdad para todas las habilidades del repositorio (como `heuristic-evaluation` e `interaction-decisions`). **Este directorio sí se versiona en Git** para que todo el equipo comparta los mismos protocolos y directivas.
* **Carpetas de adaptadores locales (`.agent/`, `.claude/`, `.cursor/`, `.opencode/`, `.pi/`):**
  Están deliberadamente ignoradas en `.gitignore`. Son proyecciones específicas generadas por las herramientas cliente para cada entorno local.
* **Cómo sincronizar las skills en tu IDE/Agente:**
  Si utilizas un editor asistido por IA (Cursor, OpenCode, Claude Code, etc.), solo necesitas ejecutar:

  ```bash
  npx @fission-ai/openspec init
  ```

  Esto proyectará las skills desde `.agents/skills/` a tu herramienta preferida sin ensuciar el control de versiones con archivos autogenerados.
* **Colaboradores o evaluadores sin herramientas de IA:**
  Las herramientas como OpenSpec, Gentle-AI, Engram o CodeGraph son **herramientas de asistencia de proceso**, no dependencias de runtime. El proyecto funciona y se evalúa de manera 100% estándar con Docker o PHP local, sin necesidad de instalar o conocer estas utilidades.
* **Documentación (`docs/`):** Contiene el historial de tareas de clase y evidencias del proyecto recibido.

---

## 5. Estándares de Código y Commits

* **Conventional Commits:** Todos los mensajes de commit deben seguir el estándar:
  * `feat: <descripción>` para nuevas funcionalidades.
  * `fix: <descripción>` para corrección de bugs.
  * `docs: <descripción>` para cambios en documentación.
  * `refactor: <descripción>` para reestructuración de código sin cambio funcional.
  * `test: <descripción>` para adición o actualización de pruebas.
  * `chore: <descripción>` para tareas de mantenimiento o configuración.
* **Sin atribución de IA (regla clave):** Ningún commit, pull request, issue, comentario ni documento puede mencionar, referenciar o atribuir contribución a una IA (por ejemplo `Co-Authored-By: Claude ...`, "Generated with ...", "con ayuda de ChatGPT", etc.). La autoría es siempre de la persona que contribuye.
* **PHP & Buenas Prácticas:** Seguir PSR-12, controladores limpios y validaciones estrictas.
* **Pruebas Automatizadas (PHPUnit):**
  * Toda nueva funcionalidad o regla de negocio (Sub-issues 1 y 2) debe incluir al menos 4 tests unitarios o de feature.
  * **Ubicación:** `tests/Unit/` (modelos, scopes y lógica de negocio) y `tests/Feature/` (rutas HTTP, controladores y sesión).
  * Ejecutar la suite completa:

    ```bash
    docker compose exec planazo_app php artisan test
    ```

  * Ejecutar un archivo puntual:

    ```bash
    docker compose exec planazo_app php artisan test tests/Unit/FiltradoPlanesTest.php
    ```

  * Filtrar por nombre de test:

    ```bash
    docker compose exec planazo_app php artisan test --filter test_filtra_por_nombre
    ```

* **Validación:** Antes de enviar un pull request o finalizar una tarea, verifica que las pruebas pasen sin errores.

---

## 6. Gestión de Issues y Pull Requests

Para mantener el orden del equipo sin burocracia innecesaria y alinearse con la metodología docente:

### Requisitos Docentes y Descomposición en Sub-issues

Cuando el docente asigne un issue con un requerimiento:

1. **El issue padre NO se cierra:** Representa la solicitud oficial del docente y permanece abierto hasta su revisión y aprobación formal en clase.
2. **Descomposición obligatoria (mínimo 3 sub-issues):** Todo requerimiento docente debe dividirse en **al menos 3 sub-issues pequeños y atómicos** para evitar tareas sobredimensionadas. La partición debe reflejar las capas de la arquitectura:
   * **Sub-issue 1 (Regla / Backend / Persistencia):** Lógica de dominio, estados, validación y persistencia en base de datos.
   * **Sub-issue 2 (Controlador / Lógica / Filtros):** Métodos de controlador, validación de parámetros, construcción de queries Eloquent y respuestas.
   * **Sub-issue 3 (Interfaz / UX / Accesibilidad):** Vistas Blade, componentes UI, estilos Tailwind, accesibilidad y estados vacíos.
3. **Relación commits ↔ sub-issues:** Cada commit debe estar directamente relacionado con su sub-issue (`Refs #<sub-issue>` o `#<sub-issue>` en el mensaje del commit).
4. **Cierre progresivo:** Los sub-issues se cierran conforme se completan y mergean sus cambios (`Closes #<sub-issue>`).
5. **Vinculación jerárquica nativa en GitHub:** Al crear cada sub-issue, además de mencionarlo en el cuerpo del issue padre, debe vincularse obligatoriamente al árbol nativo de sub-issues de GitHub (vía interfaz web o mediante GitHub CLI):

   ```bash
   gh api -X POST /repos/:owner/:repo/issues/:padre/sub_issues -F sub_issue_id=:id_global
   ```
6. **Validación obligatoria de Autenticación y Autorización (Principio de Menor Privilegio):** Todo sub-issue de backend o controlador debe auditar explícitamente el control de acceso del módulo o endpoint a implementar, garantizando que usuarios anónimos (redirección `302`) o usuarios autenticados sin privilegios o ajenos al recurso reciban un rechazo estricto (`403 Forbidden`). Es obligatorio incluir pruebas automatizadas con casos negativos (`assertStatus(403)` / `assertStatus(302)`).

### Evaluación Heurística (IHC)

Para las evaluaciones de usabilidad según las 10 Heurísticas de Nielsen (H1–H10):

* Consultar el protocolo operativo completo en la skill `heuristic-evaluation` y el informe en [docs/evaluacion-heuristica.md](docs/evaluacion-heuristica.md).
* **Reglas clave:** Único issue principal tracker (`Evaluación heurística H1–H10 · <funcionalidad>`) con lista verificable, sub-issues atómicos únicamente para hallazgos con severidad 1 a 4, y commits de cierre individuales (`fix: ... (Closes #<sub-issue>, Refs #<padre>)`).

### Decisiones de Interacción Ergonómica (IHC · Fitts, Hick y Carga Cognitiva)

Para el diseño y evaluación ergonómica de flujos e interfaces:

* Consultar el protocolo completo en la skill `interaction-decisions` y el informe en [docs/decisiones-interaccion.md](docs/decisiones-interaccion.md).
* **Estructura obligatoria:** Contrastar siempre la alternativa base o ingenua (**Antes**) frente a la solución implementada (**Después**), justificando con las fórmulas teóricas de Fitts ($T = a + b \log_2(2D/W)$), Hick ($T = b \log_2(n+1)$) y la capacidad de memoria de trabajo de Miller/Sweller ($7 \pm 2$).
* **Evidencias gráficas:** Guardadas en `docs/evidencias/historial/` respetando la nomenclatura `Fitts-antes.jpeg`, `Fitts-despues.jpeg`, `Hick-antes.jpeg`, `Hick-despues.jpeg`, `Carga-Cognitiva-antes-P1.jpeg`..`P4.jpeg`, `Carga-Cognitiva-despues.jpeg`, `Estado-Vacio-despues.jpeg`.

### Issues Generales

* Toda tarea o requerimiento fuera de los requisitos docentes debe registrarse en una issue breve con:
  * **Objetivo:** Qué se busca resolver o implementar.
  * **Criterios de Aceptación:** Qué comportamiento define que la tarea está terminada.
  * **Seguridad y Control de Acceso:** Explicitar middleware `auth`, comprobaciones en Policy y garantía de que usuarios ajenos o sin los privilegios requeridos queden completamente aislados.
  * **Pruebas esperadas:** Qué casos de prueba unitaria deben cubrirse con PHPUnit (mínimo 4 si involucra reglas de negocio o transiciones de estado, incluyendo casos negativos con `assertStatus(403)` / `302`).

### Pull Requests (PRs) y Verificación de Sub-issues

* **Destino estricto de PRs (Fork independiente):** Este repositorio es un fork que avanza de manera independiente. **Nunca envíes una PR hacia el repositorio original (upstream).**
  * **En la web:** Al pulsar "Compare & pull request", cambiá siempre el selector **base repository** a `moises-cisneros/Proyecto2IHC` y la base a `main`.
  * **Con GitHub CLI (`gh`):** Usá siempre el flag `-R moises-cisneros/Proyecto2IHC --base main`:

    ```bash
    gh pr create -R moises-cisneros/Proyecto2IHC --base main --head <rama> --title "<titulo>" --body "<cuerpo>"
    ```

* Crear una rama descriptiva vinculada a la tarea (`feat/issue-<número>`, `fix/issue-<número>`).
* **Referencias y cierre en PRs:**
  * Usar `Closes #<número-sub-issue>` para que el sub-issue completado se cierre automáticamente al mergear.
  * Usar `Refs #<número-issue-padre>` para referenciar el issue general del docente (**NUNCA usar `Closes` sobre el issue del docente**).
* La descripción de la PR no debe contener referencias ni atribuciones a ninguna IA.
* Respetar las responsabilidades de [ARCHITECTURE.md](ARCHITECTURE.md):
  * Backend: lógica, autenticación (`auth`), autorización (`PlanPolicy`) y reglas de estado en modelos y controladores.
  * Frontend: vistas Blade limpias, sin consultas a BD, protegiendo acciones con `@can`.
* **Verificación Automática (Hooks y GitHub Actions):**
  * Script de verificación cruzada local para sub-issues:

    ```bash
    node scripts/verify-subissue.mjs
    ```

  * **GitHub Action:** `.github/workflows/verify-pr-subissue.yml` bloquea cualquier PR hacia `main` si no cumple los criterios de la sub-issue o si fallan las pruebas unitarias.
  * **Flujo directo en `main`:** Si se trabaja directamente sobre la rama `main`, las verificaciones de rama de PR no bloquean el flujo diario.
