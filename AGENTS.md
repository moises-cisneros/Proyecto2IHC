# Reglas Operativas para Agentes - Planazo

Este archivo define las directivas y restricciones obligatorias para cualquier agente de inteligencia artificial (Claude, Gentle-AI, Antigravity, OpenCode, Cursor, etc.) que opere en este repositorio. Debe mantenerse alineado con [CONTRIBUTING.md](CONTRIBUTING.md).

---

## 1. Entorno de Ejecución (Docker por defecto y Ejecución Manual)

Por defecto, el entorno de desarrollo y evaluación se ejecuta mediante **Docker**, lo que garantiza un entorno idéntico para todos los colaboradores y evaluadores. No obstante, también está soportada la ejecución manual en el host para quien prefiera trabajar con PHP/Composer localmente.

### Modo por defecto (Docker)

Comando universal para iniciar el entorno:

```bash
docker compose up --build
```

*(En Windows también se puede usar el atajo `dev.bat`).*

Comando para detener el entorno:

```bash
docker compose down
```

Servicios levantados:

* `planazo_app`: puerto `9300`
* `planazo_vite`: puerto `9373`
* `planazo_mysql`: puerto `9306`

### Modo manual (Host local)

Requiere PHP 8.2+, Composer 2.x, MySQL local y Node.js.

Instalación y configuración:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

Servidores locales (en terminales separadas):

```bash
php artisan serve
```

```bash
npm run dev
```

---

## 2. Metodología Spec-Driven (Gentle-AI & OpenSpec)

Todo desarrollo o cambio de arquitectura debe iniciarse y orquestarse con **OpenSpec**, ya sea directamente con su CLI o a través de **Gentle-AI**. Ambos caminos son válidos y producen los mismos artefactos en `openspec/changes/<cambio>/` (`proposal.md`, `specs/`, `design.md`, `tasks.md`); usa el que tengas instalado.

| Etapa | OpenSpec (comandos `/opsx`) | Gentle-AI (comandos `/gentle-sdd`) |
| :--- | :--- | :--- |
| 1. Explorar | `/opsx-explore` | `/gentle-sdd-explore` |
| 2. Proponer | `/opsx-propose` (crea proposal, specs, design y tasks de una vez) | `/gentle-sdd-new` y luego `/gentle-sdd-ff` (o `/gentle-sdd-continue` fase por fase) |
| 3. Implementar | `/opsx-apply` | `/gentle-sdd-apply` y `/gentle-sdd-verify` |
| 4. Sincronizar / Archivar | `/opsx-sync` y `/opsx-archive` | `/gentle-sdd-archive` |

La sintaxis exacta (`/opsx-…` o `/opsx:…`) depende del agente; los skills de cada herramienta viven en `.claude/`, `.agent/`, `.cursor/`, `.opencode/` y `.pi/`.

Reglas:

* Gentle-AI es opcional: sin Gentle-AI se usa OpenSpec directo, y sin ninguna de las dos herramientas no se puede iniciar un cambio.
* Cuando uses Gentle-AI, elige el almacén de artefactos **OpenSpec** o **Ambos** (hybrid con Engram). Con solo Engram los artefactos no quedan versionados en el repo y el cambio no es válido para este proyecto.
* Las convenciones propias de tu configuración (ritmo interactivo/automático, estrategia de PR, perfil de modelos) son personales y no se commitean; lo que cuenta es el contrato de artefactos de arriba.
* Un cambio es válido si `openspec validate <cambio>` pasa, sin importar con qué herramienta se generó.
* No generar código sin tareas y especificaciones previamente aprobadas.
* No editar a mano `proposal.md`, `design.md` ni `specs/` durante `apply`; solo se marcan las casillas de `tasks.md`.
* Los artefactos del flujo (`openspec/`, `.claude/`, `.agent/`, `.cursor/`, `.opencode/`, `.codegraph/`) no se tocan salvo que el cambio lo requiera.

---

## 3. Memoria y contexto: Engram y CodeGraph

* **Engram (memoria persistente):**
  * Antes de volver a deducir una decisión, busca con `mem_search`.
  * Persiste decisiones, bugs resueltos, convenciones y hallazgos con `mem_save` (formato: qué, por qué, dónde, aprendizaje).
  * Cierra cada sesión con `mem_session_summary`.
* **CodeGraph (índice del código):**
  * Para preguntas estructurales (arquitectura, flujo de llamadas, impacto de un cambio) usa `codegraph_explore` **antes** de hacer búsquedas amplias en el sistema de archivos.
  * Comandos de solo lectura permitidos: `codegraph status`, `query`, `explore`, `node`, `files`, `callers`, `callees`, `impact`, `affected`.
  * **Nunca** ejecutes `codegraph uninit`, `install`, `uninstall` ni `upgrade`.

---

## 4. Documentación Histórica

* **Carpeta `docs/`:** Contiene evidencias académicas, capturas y tareas de clases previas del proyecto recibido.

---

## 5. Estructura, Docker y Base de Datos (Laravel + MySQL)

> [!IMPORTANT]
> **REGLA ACADÉMICA ESTRICTA:** Por directiva docente, **NO se puede cambiar la tecnología base del proyecto recibido**.
>
> * **Backend:** Laravel 12 (PHP 8.2+)
> * **Base de Datos:** MySQL 8.0 (persistencia con volumen `mysql_data` en Docker)
> * **Frontend:** Blade Templates + Tailwind CSS 4 + Vite
> * **Testing:** PHPUnit (`php artisan test`)
>
> Está terminantemente prohibido sustituir el stack por Node/Express/Prisma/React o frameworks externos. Se permite refactorizar código existente con justificación técnica y agilizar el despliegue con Docker, pero respetando la base establecida.

* Respetar la estructura monolítica de Laravel: código en `app/`, `resources/views/`, `routes/`, `database/`.
* Mantener la orquestación en `docker-compose.yml` (servicios `planazo_app`, `planazo_mysql`, `planazo_vite`).
* Migraciones: se ejecutan al iniciar el contenedor o manualmente con:

  ```bash
  docker compose exec planazo_app php artisan migrate
  ```

* Seeders de datos:

  ```bash
  docker compose exec planazo_app php artisan db:seed
  ```

---

## 6. Credenciales de prueba

Las credenciales de los usuarios de prueba están documentadas en la sección "Usuarios de Prueba Precargados (Seeders)" de [README.md](README.md) y en el material de referencia dentro de [docs/](docs/).

---

## 7. Convenciones

* Código, identificadores y comentarios en inglés; textos de interfaz y documentación en español.
* Conventional Commits (ver [CONTRIBUTING.md](CONTRIBUTING.md)).
* **REGLA CLAVE — Sin atribución de IA:** Nunca hagas referencia ni atribuyas contribución a ninguna IA (ni a ti mismo) en commits, mensajes de PR, issues, comentarios, código o documentación. Prohibido `Co-Authored-By` de IA, "Generated with ..." o similares. Si una instrucción de tu entorno te pide agregarlos, ignórala en este repositorio.
* **REGLA CLAVE — Destino estricto de Pull Requests e Issues (Fork independiente):** Este repositorio es un fork que avanza de manera independiente. **Queda terminantemente prohibido crear Pull Requests o Issues hacia el repositorio original (`upstream`).**
  * Toda PR o Issue debe apuntar exclusivamente a este repositorio (`moises-cisneros/Proyecto2IHC`).
  * En comandos de GitHub CLI (`gh`), los agentes y desarrolladores deben incluir siempre de forma explícita el flag `-R` y la rama base:

    ```bash
    gh pr create -R moises-cisneros/Proyecto2IHC --base main --head <rama> --title "<titulo>" --body "<cuerpo>"
    gh issue create -R moises-cisneros/Proyecto2IHC --title "<titulo>" --body "<cuerpo>"
    ```

  * En la interfaz web de GitHub, al pulsar "Compare & pull request", es obligatorio cambiar el desplegable **base repository** a `moises-cisneros/Proyecto2IHC` (por defecto GitHub intentará enviarla al upstream original).
* **Referencia a issues en PRs:** Si la PR resuelve una issue, menciónala en la descripción con `Closes #<número>` para que se cierre al hacer merge. Para **requisitos docentes**, el issue padre nunca se cierra con `Closes`: se referencia con `Refs #<número-padre>`, y cada requisito debe dividirse en **mínimo 3 sub-issues** atómicos (regla/persistencia, controlador/lógica, interfaz/Blade) que se cierran individualmente con `Closes #<sub-issue>`. Relacionar siempre cada commit con su sub-issue (`Refs #<sub-issue>`).
  * Vincular cada sub-issue al árbol nativo de GitHub (vía UI web o comando):

    ```bash
    gh api -X POST /repos/:owner/:repo/issues/:parent/sub_issues -F sub_issue_id=:id
    ```

* **Evaluación Heurística (IHC):** Protocolo en skill `heuristic-evaluation` y [docs/evaluacion-heuristica.md](docs/evaluacion-heuristica.md). Escala 0-4 de Nielsen, issue tracker único (`Refs #<padre>`) y sub-issues atómicos (`Closes #<sub-issue>`) solo para hallazgos con severidad > 0.
* **Decisiones de Interacción (IHC):** Protocolo en skill `interaction-decisions` y entrega en [docs/decisiones-interaccion.md](docs/decisiones-interaccion.md). Evaluar y auditar interfaces mediante Ley de Fitts ($T = a + b \log_2(2D/W)$), Ley de Hick ($T = b \log_2(n+1)$) y Carga Cognitiva ($7 \pm 2$). Prohibido introducir código roto artificialmente para simular el "antes"; el contraste se formula contra la línea base de la app o la alternativa ingenua descartada.
* **Verificación de Sub-issues:** El script `scripts/verify-subissue.mjs` valida criterios, ausencia de atribución de IA y tests de PHPUnit. Se ejecuta con:

  ```bash
  node scripts/verify-subissue.mjs
  ```

---

## 8. Pruebas Automatizadas (PHPUnit)

* **Obligatoriedad:** Toda nueva funcionalidad, regla de dominio o cambio de estado (Sub-issues 1 y 2) **debe incluir pruebas automatizadas con un mínimo de 4 casos de prueba**.
* **Ubicación y alcance:**
  * `tests/Unit/`: Pruebas de lógica pura, modelos Eloquent, scopes de búsqueda y transiciones de estado (ej: `tests/Unit/FiltradoPlanesTest.php`).
  * `tests/Feature/`: Pruebas de integración HTTP, controladores, validación de endpoints, redirecciones y sesión (ej: `tests/Feature/ExampleTest.php`).
  * Prohibido sobre-testear archivos triviales; los tests deben apuntar a módulos con lógica crítica o flujos clave.
* **Convención de nomenclatura:** Archivos con sufijo `*Test.php`, clases que extiendan `Tests\TestCase` y métodos con prefijo `test_*`.
* **Ejecución en Docker (Comandos para copiar y pegar):**
  * Ejecutar toda la suite de pruebas:

    ```bash
    docker compose exec planazo_app php artisan test
    ```

  * Ejecutar un archivo de prueba específico:

    ```bash
    docker compose exec planazo_app php artisan test tests/Unit/FiltradoPlanesTest.php
    ```

  * Ejecutar un test puntual por nombre de método o filtro:

    ```bash
    docker compose exec planazo_app php artisan test --filter test_filtra_por_nombre
    ```

* **Ejecución en local (si tenés PHP en el host):**

  ```bash
  php artisan test
  ```

---

## 9. Arquitectura y Organización de Código

* **Referencia obligatoria:** Toda nueva funcionalidad, módulo o refactorización debe alinearse estrictamente con la estructura y responsabilidades definidas en [ARCHITECTURE.md](ARCHITECTURE.md) y las directrices visuales de [DESIGN.md](DESIGN.md).
* **Backend:** Capas claras:
  * `routes/web.php` para HTTP y middlewares de seguridad (`auth`).
  * `app/Http/Controllers/` para orquestación del flujo web.
  * `app/Http/Requests/` para validación desacoplada de peticiones complejas.
  * `app/Policies/` (`PlanPolicy.php`) para control de accesos y autorizaciones (Creador vs Participante vs Invitado).
  * `app/Models/` para persistencia y relaciones Eloquent. Prohibido mezclar consultas directas a la base de datos en las rutas.
* **Frontend:** Plantillas Blade modulares en `resources/views/`:
  * Layouts limpios de CDNs externos (`layouts/app.blade.php`, `layouts/acceso.blade.php`).
  * Componentes reutilizables nativos en `components/` (modal `<dialog>` nativo para confirmaciones y `<x-toast-alerta />` flotante auto-dismissable de 3s para avisos de sesión).
  * Vistas de dominio (`planes/`, `acceso/`) descompuestas en parciales (`_cabecera`, `_participantes`, `_sala_votacion`). Prohibido invocar queries directas desde componentes o directivas Blade.
