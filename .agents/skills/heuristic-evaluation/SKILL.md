---
name: heuristic-evaluation
description: Directivas y flujo para ejecutar evaluaciones heurísticas (Nielsen H1–H10) en Planazo. Usar cuando se requiera evaluar usabilidad de una funcionalidad, registrar evidencias, crear o resolver sub-issues de hallazgos.
license: MIT
metadata:
  author: Planazo Team
  version: "1.0"
---

# Evaluación Heurística (IHC) — Protocolo Planazo

## 1. Alcance y Escala de Severidad (Nielsen)

- La evaluación se realiza por **funcionalidad específica**, analizando de forma sistemática las 10 Heurísticas de Jakob Nielsen (H1 a H10).
- **Escala de Severidad (0 a 4):**
  - **0 (Sin problema):** Cumple satisfactoriamente en código e interfaz.
  - **1 (Cosmético):** Detalle estético menor; se corrige si hay tiempo disponible.
  - **2 (Menor):** Dificulta levemente la interacción, pero permite al usuario continuar.
  - **3 (Mayor):** Problema importante de usabilidad; genera confusión o incertidumbre grave.
  - **4 (Catastrófico):** Bloquea por completo la concreción de la tarea.

## 2. Gestión de Issues en GitHub

- **Issue Principal (Tracker):**
  - Título: `Evaluación heurística H1–H10 · <funcionalidad>`.
  - Contiene la lista verificable de H1 a H10 (las que cumplen explican por qué; las que fallan referencian sus sub-issues).
  - Permanece **abierto** durante el desarrollo y se cierra al completar la implementación.
- **Sub-issues Atómicos (Solo para hallazgos con severidad > 0):**
  - No crear 10 issues al inicio. Solo se crean sub-issues para los hallazgos que requieren corrección en código.
  - Cada sub-issue debe ser conciso pero auto-contenido:
    1. Heurística, severidad y funcionalidad evaluada (`Refs #<issue-padre>`).
    2. **Antes (Problema observable):** Comportamiento actual en código y pantalla.
    3. **Después (Mejora propuesta):** Solución técnica concreta alineada a `DESIGN.md` (sin librerías superfluas).
    4. **Criterios de Aceptación:** Checklist de verificación (UI, tests automatizados en PHPUnit, capturas).
    5. **Archivos afectados.**
    6. **Commit de cierre:** `fix: <descripción> (Closes #<sub-issue>, Refs #<issue-padre>)`.

## 3. Informe de Auditoría y Evidencias

- **Documento oficial:** `docs/evaluacion-euristica.md` (nombre real del archivo en el repo; no crear un duplicado con otra ortografía)
  - Resumen ejecutivo con tabla de 5 columnas: `Observación | Heurística | Severidad (0-4) | Mejora | Comprobación`.
  - Registro detallado H1 a H10 con código fuente y capturas embebidas.
  - Toda heurística debe terminar en estado **«Cumple»** o **«Corregida»**.
- **Evidencias gráficas:** Guardadas en `docs/evidencias/` con nomenclatura estándar (`H<num>-antes.png`, `H<num>-despues.png`).
- **Sin atribución de IA:** Prohibido incluir `Co-Authored-By` o menciones de IA en commits, issues o PRs.

## 4. Decisiones Vigentes y Criterios de Severidad del Proyecto

* **Auditoría de todo el repo:** cuando se evalúa el repositorio completo (no una sola funcionalidad), se recorre la **lista de pantallas** de `resources/FLOWS.md` (§2.1) (acceso, `/mis-planes`, sala, editar, historial, Mi Cuenta, analítica) y se registra cada hallazgo con su pantalla.
* **Salidas y bloqueos (H3, H5, H6, H9):** se evalúan con la prueba de simetría y el catálogo de la skill `dark-patterns`. Un bloqueo sin explicación ni acción de salida es severidad >= 3.
* **Reglas de negocio evadibles por una ruta paralela** (un endpoint que hace menos validaciones que el oficial) es severidad 4: rompe H5 (prevención de errores) y la integridad de datos. Corregir consolidando en la ruta con las invariantes, no parchando la copia.
* **Evidencia mínima por hallazgo:** referencia `archivo:línea`, heurística, severidad 0–4, y test (PHPUnit o Playwright) que lo cubra.
* **Estados canónicos del plan:** `pendiente`, `confirmado`, `cancelado`. Cualquier otro valor de estado en código o copy es un hallazgo de consistencia (H4).
* **ARIA válido por rol:** `aria-pressed` solo en botones; en enlaces de navegación/filtro usar `aria-current="page"` en el activo. Un atributo ARIA inválido para el rol es hallazgo de severidad 1 (H4/accesibilidad) y debe tener test.
* **Specs desactualizados cuentan como hallazgo:** un test E2E que ya no coincide con la UI (títulos, textos, diálogos) es deuda de verificación; se corrige junto con el cambio de interfaz.
* **Enlaces que prometen una acción deben llevar a ella:** un «Liquidar» que aterriza en otra pestaña rompe H5/H7 aunque el botón exista. Cada enlace de resolución de la Salida Honesta se verifica con un test que comprueba el destino (`tab=gastos`).
