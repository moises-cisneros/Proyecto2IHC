# ODD Feature: Refactor de usabilidad, trazabilidad bilateral y refinamiento de pagos (Issue #93)

Estado: EN PROGRESO — Orquestación ODD para Issue Madre #93 y Sub-issues #94, #95 y #96

## Specs

- S1: "CA-01 (Trazabilidad Bilateral en Historial): Cuando un acreedor confirma o rechaza un pago, la actividad queda registrada y es visible para ambos involucrados (deudor y receptor) en su respectivo `/historial`."
- S2: "CA-02 (Normalización Monetaria a Bs): Erradicar cualquier símbolo `$` remanente en las descripciones de actividad de pagos y gastos, usando de forma consistente `Bs {{ $pago->monto }}`."
- S3: "CA-03 (Filtro Temático de Gastos): `HistorialController` incorpora la categoría `gastos` filtrando acciones de gastos, pagos y confirmaciones/rechazos."
- S4: "CA-04 (Ergonomía en Modal de Pago - Hick y Fitts): Selector explícito de intención: «Pagar totalidad de la deuda (Bs XX.XX)» (campo bloqueado con valor exacto) vs «Pago parcial» (campo habilitado con advertencia de saldo restante)."
- S5: "CA-05 (Auditoría Asistida por IA): Si el comprobante subido coincide al 100% con la deuda adeudada, el modal de revisión muestra un badge verde de verificación y prioriza el botón de confirmación en 1 clic."
- S6: "CA-06 (Seguridad y Menor Privilegio): Validación estricta de que el monto no exceda la deuda exigible y verificación de permisos con `Gate::authorize()`."

## Tasks

- [x] T1 (S1, S2): [Backend] Migración para destinatario_id en plan_activities, visibilidad bilateral en PlanActivity y descripciones normalizadas a Bs en GastosController (Route: inline, Commit: 6e0e0ca, PR: #105 - MERGED)
- [ ] T2 (S3, S6): [Controlador] Filtro categoria=gastos en HistorialController y validación de monto máximo exigible en StorePagoRequest (Route: inline, Commit: pending)
- [ ] T3 (S4, S5): [Interfaz] Rediseño ergonómico de modal de pago con selector total/parcial y badge asistido por IA (Route: inline, Commit: pending)

## Log

- L1: "Implementa las subissues de la issu #93 con odd de gentle-ai, empieza una subissue con su PR, luego mergeas y empiezas con la siguiente hasta terminar toda la issue, el odd es de la issue #93 comprendida en sus subissues."
