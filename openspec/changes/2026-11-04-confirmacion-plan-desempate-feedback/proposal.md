# Propuesta: Confirmar Plan a partir de Votación, Resolución de Empates y Mejoras por Feedback

## Referencia

- **Issue Madre:** #45 — `[Requisito] — Tarea 7: Confirmar Plan a partir de Votación, Resolución de Empates y Mejora por Feedback`
- **Sub-issues Asociadas:**
  - #46 — `[Feedback IHC] — Validación de fechas pasadas y confirmación explícita en selector de fechas (Refs #45)`
  - #47 — `[Backend / Persistencia] — Migración, campos de opción ganadora, motivo de desempate y estado confirmado (Refs #45)`
  - #50 — `[Controlador / Lógica] — Endpoint de cierre, resolución de empate con motivo y autorización estricta (Refs #45)`
  - #48 — `[Interfaz / Blade] — Modal de desempate con motivo, banner de veredicto público y controles protegidos (Refs #45)`
  - #49 — `[Pruebas y Evidencias] — Suite Playwright para escenario de desempate y documentación en evidencias/tarea-7.md (Refs #45)`

## Contexto

En la versión actual de Planazo, los participantes pueden proponer fechas y lugares, y emitir múltiples votos sobre las opciones en sala abierta. Sin embargo, no existe un mecanismo formal para que el organizador cierre la sala deliberativa, confirme las opciones ganadoras y resuelva democráticamente situaciones de empate con justificación explícita.

Asimismo, durante la sesión de evaluación por pares (Clase 19), se identificaron dos fallas críticas de interacción y usabilidad:

1. **UniSport (Juan Carlos Laura Céspedes):** Pérdida de selección en el selector de fecha/hora por falta de botón explícito «Aceptar fecha».
2. **Mascota al Día (Choque Benavides):** Posibilidad de crear y actualizar planes con fechas límite en el pasado sin validación en frontend ni backend.

## Objetivos Generales

1. Implementar las mejoras de usabilidad por feedback (Sub-issue #46) garantizando prevención de errores (Nielsen H5), retención reactiva de fechas y validación estricta contra fechas pasadas.
2. Incorporar persistencia de esquema y métodos de modelo en Eloquent (Sub-issue #47) para registrar las opciones ganadoras (`propuesta_fecha_id`, `propuesta_lugar_id`), el `motivo_desempate`, la fecha de confirmación y algoritmos de detección de empate.
3. Exponer el endpoint transaccional de confirmación y desempate (Sub-issue #50) protegido mediante `ConfirmarPlanRequest` y `PlanPolicy@confirmar`, exigiendo motivo obligatorio ($\ge 5$ caracteres) ante empates.
4. Diseñar la interfaz accesible en Blade (Sub-issue #48) con modal nativo `<dialog>`, banner público de veredicto, controles de votación inhabilitados e inmutables, affordance de acceso a la sala en el listado sin desbordar el contenedor y redimensionamiento armónico del avatar del organizador en cabecera.
5. Desarrollar las 3 suites Playwright E2E y suites PHPUnit independientes (Sub-issue #49), documentando los dos commits de entrega (`fix` y `feat`) y evidencias en `evidencias/tarea-7.md`.
