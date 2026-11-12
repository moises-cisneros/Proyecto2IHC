# ODD Feature: Analítica de interacción en toda la app y funcionalidad de retirarse de un plan (Issue #97)

Estado: EN PROGRESO — Orquestación ODD para Issue Madre #97 y Sub-issues #98 a #104 (Alcance actual: #98 a #102)

## Specs

- S1: "CA-01 (Analítica Multi-Embudo): Mapeo e instrumentación de los 6 macroflujos de la app (creación, votación, invitaciones, gastos, retiro y filtros) en el modelo Evento y visualización en `/analitica`."
- S2: "CA-02 (Captura de Errores y Puntos de Freno): Registro estructurado de fallas de validación y permisos denegados con lista fija de motivos (`deuda_pendiente`, `fecha_invalida`, `voto_duplicado`, etc.) y búsquedas sin resultados."
- S3: "CA-03 (Acceso de Telemetría Aislado): La ruta `/analitica` devuelve `403 Forbidden` en producción. En desarrollo (`local`/`testing`), se accede mediante la Dev Floating Toolbar en la esquina inferior (`dev_toolbar.blade.php`), manteniendo el navbar del usuario limpio (Nielsen H8)."
- S4: "CA-04 (Regla de Dominio - Organizador): El organizador del plan no puede retirarse (`403 Forbidden` / DomainException); debe cancelar o eliminar el plan."
- S5: "CA-05 (Regla de Dominio - Bloqueo por Deuda en Gastos): Si el participante adeuda saldos netos en `Plan::calcularSaldosNetos()`, el retiro queda estrictamente bloqueado con feedback explicativo y enlace directo a saldar su deuda."
- S6: "CA-06 (Consecuencia de Retiro - Recálculo de Votos): Al confirmarse el retiro antes del cierre, todos los votos emitidos por el usuario en opciones del plan se eliminan, las opciones recalculan su conteo dinámicamente y las propuestas ganadoras se actualizan en tiempo real."
- S7: "CA-07 (Testing E2E Playwright y Evidencias): Suite Playwright con demostración de anulación de votos y bloqueo por deuda, reporte de evidencias y auditoría de diseño."

## Tasks

- [x] T1 (S1): [Backend] Motor analítico multi-embudo y agregación de métricas en modelo Evento (Sub-issue #98)
- [ ] T2 (S1, S2, S3): [Controlador] Instrumentación de eventos en controladores y endpoint seguro /analitica (Sub-issue #99)
- [ ] T3 (S1, S3): [Interfaz] Rediseño del dashboard de analítica, Dev Floating Toolbar y cliente analitica.js (Sub-issue #100)
- [ ] T4 (S4, S5, S6): [Backend] Dominio de retiro de participantes, anulación de votos y bloqueo estricto por deuda (Sub-issue #101)
- [ ] T5 (S4, S5): [Controlador] Endpoint POST /retirarse, Form Request y Policy de autorización (Sub-issue #102)
- [ ] T6 (S5, S6): [Interfaz] Botón accesible de retiro, diálogo de advertencia y banner de bloqueo por deuda (Sub-issue #103 - Pendiente para siguiente fase)
- [ ] T7 (S7): [Testing/Docs] Suite Playwright E2E de retiro/analítica, reporte de evidencias y auditoría de diseño (Sub-issue #104 - Pendiente para siguiente fase)

## Log

- L1: "Inicio de orquestación ODD para Issue #97 y sub-issues #98 a #104, programando implementación secuencial de sub-issues #98 a #102."
