# Matriz de Evidencias — Tarea 8: Proceso de Gastos y Liquidación con Estados y Consecuencias

Este documento constituye la memoria técnica y entrega formal de la **Tarea 8 (Proceso de Gastos y Liquidación con Estados y Consecuencias)** del proyecto Planazo, desarrollada bajo las directivas de la Clase 20.

---

## 📋 Consigna y Requisitos Docentes

```text
Proceso de Gastos y Liquidación con Estados y Consecuencias:
1. División de gastos: Registro de gastos con división en partes iguales o personalizada (suma exacta al centavo).
2. Cálculo reactivo de saldos netos entre participantes (quién debe a quién y cuánto).
3. Registro de pagos con consecuencia de estado (pendiente -> confirmado / rechazado con motivo).
4. Inmutabilidad y autorización: El deudor no puede auto-confirmarse su pago; solo el receptor puede confirmarlo o rechazarlo.
```

- **Fork oficial de entrega:** `moises-cisneros/Proyecto2IHC`
- **Issue Padre:** [Issue #65](https://github.com/moises-cisneros/Proyecto2IHC/issues/65)
- **Árbol de Sub-issues Atómicas:**
  - [Sub-issue #66](https://github.com/moises-cisneros/Proyecto2IHC/issues/66): `[Backend] — Migraciones, modelos Gasto, GastoDivision, Pago, relaciones Eloquent y cálculo de saldos`
  - [Sub-issue #67](https://github.com/moises-cisneros/Proyecto2IHC/issues/67): `[Controlador] — Endpoints de gastos y pagos, validaciones Form Request, transiciones de estado e idempotencia`
  - [Sub-issue #68](https://github.com/moises-cisneros/Proyecto2IHC/issues/68): `[Interfaz] — Pestaña de Gastos en sala, modal de registro con división dinámica y panel de liquidación`
  - [Sub-issue #69](https://github.com/moises-cisneros/Proyecto2IHC/issues/69): `[Testing] — Suite Playwright para escenario docente de 3 participantes, 2 gastos, división personalizada y pago confirmado`

---

## 🏷️ Resumen de PRs y Commits por Capa

| Sub-issue | Capa | PR en `main` | Descripción de la Entrega |
| :--- | :--- | :--- | :--- |
| **#66** | Backend / Persistencia | [PR #73](https://github.com/moises-cisneros/Proyecto2IHC/pull/73) | Migraciones `gastos`, `gasto_divisiones`, `pagos`, modelos Eloquent con casts y método de dominio `Plan::calcularSaldosNetos(): array` para resolución de deudas cruzadas. |
| **#67** | Controlador / Seguridad | [PR #74](https://github.com/moises-cisneros/Proyecto2IHC/pull/74) | `GastosController`, `GastoPolicy`, `PagoPolicy`, `StoreGastoRequest` (validación de sumas y anti-IDOR), `StorePagoRequest` y `RechazarPagoRequest`. |
| **#68** | Interfaz / Blade / IHC | [PR #75](https://github.com/moises-cisneros/Proyecto2IHC/pull/75) | Pestaña «Gastos y Liquidación» en la sala del plan, parcial `_gastos_liquidacion.blade.php`, tarjetas de posición neta («Debes» vs «Te deben»), modales `<dialog>` dinámicos y ergonomía Fitts ($\ge 44$px). |
| **#69** | Testing E2E / Evidencias | [PR #...](https://github.com/moises-cisneros/Proyecto2IHC/pull/...) | Suite Playwright `e2e/tarea-8-gastos-liquidacion.spec.ts` con 3 participantes, división personalizada, pago pendiente y confirmado con actualización de balances. |

---

## 📊 Matriz Comparativa: Antes vs Después de la Tarea 8

| Dimensión / Regla | Estado Inicial (Línea Base) | Estado Final (Implementación Tarea 8) | Justificación Ergonómica / Seguridad |
| :--- | :--- | :--- | :--- |
| **Gestión Financiera** | Inexistente; los participantes solo acordaban fechas y lugares sin control de gastos compartidos. | Pestaña integrada «Gastos y Liquidación» en la sala del plan con cálculo consolidado de deudas bilaterales netas. | **Nielsen H6 (Reconocer antes que recordar):** Reduce la carga cognitiva al mostrar directamente «Le debes a X: $Y» sin forzar cálculos manuales externos. |
| **División de Gastos** | No disponible. | Selector simple de modo (Equitativa vs Personalizada) con validación interactiva en tiempo real y en backend al centavo. | **Ley de Hick ($n \le 4$):** Opciones directas; inputs personalizados se revelan por divulgación progresiva. |
| **Liquidación de Deudas** | Sin registro. | Flujo con consecuencia de estados: `pendiente` $\to$ `confirmado` / `rechazado`. | **Integridad y Menor Privilegio:** Un deudor no puede extinguir su deuda sin la aprobación explícita del acreedor (403 Forbidden). |
| **Rechazo de Pagos** | No disponible. | Modal accesible que exige `motivo_rechazo` ($\ge 5$ caracteres) y mantiene intacta la deuda. | **Nielsen H9 (Ayuda en errores):** Explicación transparente de por qué no se acreditó una transferencia. |
| **Controles de Acción** | N/A | Botones táctiles mínimos de $44 \times 44\text{px}$ y separación antagónica $\ge 12\text{px}$ entre Confirmar y Rechazar. | **Ley de Fitts:** Mitiga toques accidentales y optimiza el uso en dispositivos móviles y de escritorio. |

---

## 📸 Evidencias de Ejecución y Capturas

Las capturas generadas por la suite E2E se almacenan en `docs/evidencias/tarea-8/`:

1. **`01-registro-gasto.png`:** Registro de gasto equitativo por parte del organizador (`Lucas`).
2. **`02-division-personalizada.png`:** Formulario interactivo con división personalizada calculada al centavo por `Ana`.
3. **`03-pago-pendiente.png`:** Pago emitido por el deudor (`Carlos`) en estado `pendiente`, sin alterar los saldos netos aún.
4. **`04-pago-confirmado-saldos.png`:** Panel del receptor (`Ana`) tras confirmar el pago, reflejando la actualización inmediata de saldos y la extinción de la deuda.

---

## 🧪 Verificación de Suites de Pruebas

### 1. PHPUnit (Backend y Controlador)
- `tests/Unit/GastosPersistenciaTest.php` (4 casos de persistencia, división equitativa, personalizada y deudas cruzadas).
- `tests/Feature/GastosControllerTest.php` (5 casos de endpoints, autorización 403, idempotencia y redirección 302).
- `tests/Feature/GastosVistasTest.php` (2 casos de interfaz y visibilidad condicional con `@can`).

### 2. Playwright E2E
- `e2e/tarea-8-gastos-liquidacion.spec.ts` (Flujo multi-usuario docente completo con 3 participantes del seeder).
