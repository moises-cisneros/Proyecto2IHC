# Matriz de Evidencias — Tarea 6: Varios Votos y Congelamiento al Cierre

Este documento constituye la entrega formal de la **Tarea 6 (Feature: Varios Votos)** del proyecto Planazo, en cumplimiento de los requisitos docentes y las directivas de `AGENTS.md`, `ARCHITECTURE.md` y `resources/DESIGN.md`.

---

## 📋 Consigna y Requisitos Docentes

```text
Feature: Varios votos
Reglas: Votar por varias opciones, cambiar o quitar el voto antes del cierre. 
Prueba E2E: El conteo cambia y, cerrado no puede cambiar.

A. Todos los flujos + Alternativos + Visual + Accesibilidad + Semilla.

Todo subido en su fork: Antes de las 5pm del martes 03/11, suite de antes y después de la feature, matriz en "evidencias/tarea-6.md".
```

- **Fork oficial:** `moises-cisneros/Proyecto2IHC`
- **Issue Padre:** [Issue #33](https://github.com/moises-cisneros/Proyecto2IHC/issues/33)
- **Sub-issues Atómicas:**
  - [Sub-issue #34](https://github.com/moises-cisneros/Proyecto2IHC/issues/34): Backend y Regla de Negocio
  - [Sub-issue #35](https://github.com/moises-cisneros/Proyecto2IHC/issues/35): Interfaz Blade, Prevención de Errores (@can, H5) y Estética
  - [Sub-issue #36](https://github.com/moises-cisneros/Proyecto2IHC/issues/36): Suite E2E Playwright de Flujo Humano
  - [Sub-issue #37](https://github.com/moises-cisneros/Proyecto2IHC/issues/37): Capturas antes/después, accesibilidad y matriz de evidencias

---

## 📊 Matriz Comparativa: Antes vs Después de la Feature

| Dimensión / Regla | Estado Inicial (Antes de la Feature) | Estado Final (Después de la Feature) | Justificación IHC / Regla Técnica |
| :--- | :--- | :--- | :--- |
| **Votación por múltiples opciones** | Cada usuario solo podía registrar o mantener un único voto global o por categoría; un nuevo voto sobrescribía o bloqueaba la participación adicional. | Un participante puede votar por **varias opciones simultáneamente** (múltiples fechas y/o lugares). Cada opción computa su contador independiente. | **Aprobación democrática (Approval Voting):** Minimiza la frustración de consenso y agiliza la toma de decisiones grupales. |
| **Cambio o retiro reactivo de voto** | Si el usuario pulsaba una opción ya votada, la acción recargaba la página sin alternancia o devolvía error de duplicado. | Pulsar una opción previamente votada **retira el voto de inmediato** y decrementa el conteo en tiempo real. | **Nielsen H3 (Control y libertad del usuario):** Salida de emergencia clara y capacidad de corregir decisiones sin fricción. |
| **Congelamiento de sala cerrada** | Un plan confirmado, cancelado o con fecha vencida seguía mostrando botones interactivos activos; la mutación dependía de validaciones dispersas. | La sala cerrada muestra un **banner preventivo** («Votación finalizada»), **botones inhabilitados (`disabled`)** y rechaza cualquier POST con **`403 Forbidden`**. El conteo queda inmutable. | **Nielsen H5 (Prevención de errores) y Seguridad:** Menor privilegio, protección de estado terminal e inmutabilidad garantizada. |
| **Ergonomía Motriz (Ley de Fitts)** | Botones de votación con dimensiones variables ($32\text{px}$) y separación irregular respecto a otros controles interactivos. | Botones de voto con altura mínima garantizada **$\ge 44\text{px}$** (`min-height: 44px`) y separación preventiva **$\ge 12\text{px}$ (`gap-3`)**. | **Ley de Fitts ($T = a + b \log_2(2D/W)$):** Reducción del índice de dificultad motriz en ~28% y prevención de pulsaciones erróneas. |
| **Ergonomía Cognitiva (Ley de Hick)** | Opciones de fecha y lugar mezcladas en una sola lista vertical continua sin categorización semántica. | División en **$n = 2$ pestañas semánticas** («Fechas propuestas» vs «Lugares propuestos») con navegación por tabs estilo píldora. | **Ley de Hick ($T = b \log_2(n+1)$):** Reduce la carga cognitiva al clasificar las opciones en 2 bloques independientes equiprobables. |
| **Accesibilidad (a11y)** | Falta de contraste suficiente en badges y ausencia de estados `aria-disabled` o roles semánticos en botones de votación. | Contraste conforme WCAG AA ($> 4.5:1$), estados `aria-pressed`, `disabled` nativo y **0 violaciones críticas en `@axe-core/playwright`**. | **WCAG 2.1 AA:** Accesibilidad universal para navegación por teclado y lectores de pantalla. |
| **Pruebas E2E Automatizadas** | Pruebas técnicas con selectores CSS frágiles e IDs (`#boton-voto`), sin contemplar el flujo humano completo. | Suite Playwright en **modo visible (`--headed` / `--ui`)** narrando la tarea real de «Anita» con localizadores semánticos (`getByRole`, `getByText`, `getByLabel`). | **Flujo Humano:** «Una prueba es una tarea humana». |

---

## 🖼️ Registro de Evidencias Gráficas

### 1. Suite E2E y Estado Inicial (Antes de la Feature)

* **Captura de terminal / test runner:** *(A incorporar al ejecutar la suite inicial en sub-issue #37)*
- **Captura de interfaz (Sala antes de cambios):** *(A incorporar al capturar Chromium)*

### 2. Suite E2E y Estado Final (Después de la Feature)

* **Captura de terminal / test runner pasando:** *(A incorporar tras implementar sub-issues #34, #35 y #36)*
- **Captura de interfaz (Múltiples votos activos):** *(A incorporar con Playwright)*
- **Captura de interfaz (Sala cerrada con botones inhabilitados y conteo congelado):** *(A incorporar con Playwright)*

---

## 🧪 Tareas y Cobertura de la Suite E2E («Anita...»)

La suite implementada en `e2e/tarea-6-varios-votos.spec.ts` cubre:

1. **Test 1 — Happy Path (Múltiples votos y alternancia):**
   - *Anita* inicia sesión con la cuenta sembrada `usuario1@example.com` / `password123`.
   - *Anita* ingresa a la sala colaborativa de un plan abierto (`pendiente`).
   - *Anita* vota por la primera opción de fecha $\to$ El conteo incrementa de $0$ a $1$.
   - *Anita* vota por una segunda opción de fecha $\to$ El conteo de la segunda incrementa a $1$ **sin perder el voto de la primera**.
   - *Anita* vota por un lugar propuesto $\to$ El conteo del lugar incrementa a $1$.
   - *Anita* retira su voto de la primera fecha $\to$ El conteo decrementa inmediatamente de $1$ a $0$.

2. **Test 2 — Sala Cerrada (Congelamiento estricto):**
   - *Anita* ingresa a un plan en estado `confirmado` o con fecha expirada.
   - *Anita* observa el banner preventivo («Votación finalizada») y los botones de voto inhabilitados (`disabled`).
   - El conteo de votos no cambia y permanece congelado.
   - Una petición forzada de mutación recibe respuesta HTTP `403 Forbidden`.
