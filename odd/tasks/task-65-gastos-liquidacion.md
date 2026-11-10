# ODD Task: Proceso de Gastos y Liquidación (Issue #65)

## Estado General

- **Issue Madre:** #65 — `[Requisito] — Tarea 8: Proceso de Gastos y Liquidación con Estados y Consecuencias`
- **Sub-issues:**
  - #66: `[Backend] — Migraciones, modelos Gasto, GastoDivision, Pago, relaciones Eloquent y cálculo de saldos`
  - #67: `[Controlador] — Endpoints de gastos y pagos, validaciones Form Request, transiciones de estado e idempotencia`
  - #68: `[Interfaz] — Pestaña de Gastos en sala, modal de registro con división dinámica y panel de liquidación`
  - #69: `[Testing] — Suite Playwright para escenario docente de 3 participantes, 2 gastos, división personalizada y pago confirmado`

---

## Tareas Atómicas y PRs Integrados

### 1. Sub-issue #66 (Backend / Persistencia)

- [x] Branch: `feat/issue-66-backend-gastos-modelos-saldos`
- [x] Migraciones: `create_gastos_table`, `create_gasto_divisiones_table`, `create_pagos_table`.
- [x] Modelos: `Gasto`, `GastoDivision`, `Pago` con relaciones y casts.
- [x] Método de dominio: `Plan::calcularSaldosNetos(): array` para cálculo de deudas cruzadas.
- [x] Tests unitarios: `tests/Unit/GastosPersistenciaTest.php` (4 casos passing).
- [x] PR en `main`: [PR #73](https://github.com/moises-cisneros/Proyecto2IHC/pull/73) (MERGED, Closes #66).

### 2. Sub-issue #67 (Controlador / Seguridad)

- [x] Branch: `feat/issue-67-controlador-gastos-pagos-seguridad`
- [x] Form Requests: `StoreGastoRequest`, `StorePagoRequest`, `RechazarPagoRequest`.
- [x] Policies: `GastoPolicy`, `PagoPolicy`.
- [x] Controlador: `GastosController` (`storeGasto`, `storePago`, `confirmarPago`, `rechazarPago`).
- [x] Tests feature: `tests/Feature/GastosControllerTest.php` (5 casos passing con 302 y 403).
- [x] PR en `main`: [PR #74](https://github.com/moises-cisneros/Proyecto2IHC/pull/74) (MERGED, Closes #67).

### 3. Sub-issue #68 (Interfaz / Blade / IHC)

- [x] Branch: `feat/issue-68-interfaz-gastos-liquidacion-blade`
- [x] Vista parcial: `resources/views/planes/_gastos_liquidacion.blade.php`.
- [x] Integración en `detalle.blade.php` y `_sala_votacion.blade.php`.
- [x] Tarjetas de balance neto, deudas bilaterales («Debes» vs «Te deben»).
- [x] Modales nativos `<dialog>` para registro de gastos y rechazo de pagos.
- [x] Tests feature: `tests/Feature/GastosVistasTest.php` (2 casos passing).
- [x] PR en `main`: [PR #75](https://github.com/moises-cisneros/Proyecto2IHC/pull/75) (MERGED, Closes #68).

### 4. Sub-issue #69 (Testing E2E / Evidencias)

- [x] Branch: `test/issue-69-e2e-gastos-liquidacion-evidencias`
- [x] Suite Playwright: `e2e/tarea-8-gastos-liquidacion.spec.ts` (flujo multi-usuario con 3 participantes).
- [x] Documento de evidencias: `docs/evidencias/tarea-8.md`.
- [x] Capturas automáticas en `docs/evidencias/tarea-8/`.
- [x] PR en `main`: [PR #76](https://github.com/moises-cisneros/Proyecto2IHC/pull/76) (MERGED, Closes #69).
