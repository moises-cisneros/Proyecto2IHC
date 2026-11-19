# 🧪 Reporte Oficial de Pruebas Automatizadas y Cobertura (IHC 2026-2)

Este directorio almacena la evidencia de ejecución, métricas de cobertura y los reportes interactivos de las suites de prueba de **Planazo**, dando cumplimiento estricto a los criterios de evaluación del Proyecto 2 (Refs #128, Refs #132).

---

## 📊 Resumen Ejecutivo de Pruebas

| Suite de Pruebas | Entorno / Herramienta | Pruebas Ejecutadas | Resultado | Evidencia / Reporte |
| :--- | :--- | :--- | :--- | :--- |
| **Pruebas Unitarias y de Integración** | PHPUnit 11 + PCOV | **283 pruebas** (1,347 aserciones) | ✅ 100% Pasadas | [`reportes/coverage/html/index.html`](reportes/coverage/html/index.html) |
| **Pruebas End-to-End (E2E)** | Playwright (Chromium) | **30 pruebas** (13 specs) | ✅ 100% Pasadas | [`reportes/playwright-report/index.html`](reportes/playwright-report/index.html) |
| **Cobertura Diferencial del Fork** | `diff-cover` (vs commit inicial `731fb8d`) | **1,836 líneas agregadas** | ✅ 89% Cobertura Real | [`reportes/diff-coverage.html`](reportes/diff-coverage.html) |
| **Integración Continua (CI)** | GitHub Actions (`pruebas.yml`) | Pipeline Completo | ✅ En Verde (✓) | [Ver Pipeline en GitHub Actions](https://github.com/moises-cisneros/Proyecto2IHC/actions) |

---

## 📁 Estructura de Reportes en el Repositorio

Los reportes completos generados por la integración continua y herramientas locales están disponibles directamente en:

```text
docs/entregables/pruebas/
├── README.md                          # Este documento resumen con instrucciones
└── reportes/
    ├── diff-coverage.html             # Reporte interactivo HTML de diff-cover sobre líneas del fork
    ├── diff-coverage.md               # Reporte detallado en Markdown de diff-cover
    ├── coverage/
    │   ├── clover.xml                 # Archivo Clover XML con métricas de cobertura para PHPUnit
    │   └── html/
    │       └── index.html             # Dashboard interactivo HTML de cobertura de código
    └── playwright-report/
        └── index.html                 # Reporte visual interactivo de Playwright con trazas y pasos
```

---

## 🛠️ Comandos de Reproducción Rápida

### 1. Generar Reporte de Cobertura del Fork (`diff-cover`)

Para evaluar únicamente las líneas de código añadidas en este fork:

```bash
# 1. Asegurar dependencia diff-cover instalada
pip install diff-cover

# 2. Ejecutar diff-cover contra el archivo clover.xml comparando con la base del fork
diff-cover docs/entregables/pruebas/reportes/coverage/clover.xml --compare-branch 731fb8d
```

Para generar un nuevo reporte en HTML y Markdown:

```bash
diff-cover docs/entregables/pruebas/reportes/coverage/clover.xml --compare-branch 731fb8d --format html:docs/entregables/pruebas/reportes/diff-coverage.html --format markdown:docs/entregables/pruebas/reportes/diff-coverage.md
```

### 2. Ejecutar la Suite de Pruebas Unitarias y de Integración (PHPUnit)

```bash
# En entorno Docker (recomendado):
docker compose exec planazo_app php artisan test --coverage

# En Host local:
php artisan test --coverage
```

### 3. Ejecutar la Suite de Pruebas E2E (Playwright)

```bash
# Modo headless (predeterminado):
pnpm run test:e2e

# Con navegador visible:
pnpm run test:e2e:headed

# Interfaz visual interactiva:
pnpm run test:e2e:ui
```
