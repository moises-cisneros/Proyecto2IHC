# ODD Feature: Mis datos, salida honesta y eliminación de cuenta/planes sin patrones oscuros (Issue #116)

Estado: EN PROCESO — Orquestación ODD para Issue Madre #116 y Sub-issues #117, #118, #119 y #120.

## Specs

- S1: "Portabilidad de datos (JSON/CSV): El usuario puede ver sus datos personales/colaborativos y descargarlos en formato estructurado (JSON o CSV) sin hashes ni tokens sensibles."
- S2: "Regla de dominio de eliminación de cuenta: Si organiza planes abiertos (propuesta, votando o pendiente), debe transferirlos o cancelarlos (planes_abiertos_organizados). Con deudas pendientes en gastos (deudas_pendientes_gastos), no puede eliminarla. Una vez eliminada, se inhabilitan credenciales y se impide el login futuro."
- S3: "Salida honesta al eliminar un plan con pendientes: Si un organizador intenta eliminar un plan con gastos no saldados o confirmado con participantes, el sistema no lo destruye en silencio ni usa botones mudos/disabled opacos; explica claramente por qué y qué hacer (liquidar deudas, transferir organizador o cancelar formalmente con aviso)."
- S4: "Confirmación honesta: Modales de salida con prueba de simetría estricta, botones del mismo tamaño y peso visual (>= 44px), separación antagónica adecuada, consecuencias explicadas sin chantaje emocional ni culpa (sin confirmshaming) ni pasos innecesarios."
- S5: "Rechazo simétrico de invitaciones: En la sala/lista de participantes, el invitado cuenta con botón simétrico 'Rechazar invitación' para no quedar atrapado en el plan."
- S6: "Desmantelamiento de opciones preseleccionadas: En modalidad de pago de gastos, eliminar el default opt-in rígido para permitir una deliberación económica neutral."
- S7: "Telemetría sin PII: Registrar eventos de telemetría de interacción para inicio, bloqueo y éxito de salida de cuenta y plan, sin persistir nombres, emails ni datos personales."
- S8: "Testing automatizado y evidencias: Suites PHPUnit (tests/Feature/SalidaHonestaTest.php) y Playwright E2E (e2e/tarea-10-datos-salida-honesta.spec.ts) pasando al 100%, y reporte estructurado en docs/evidencias/tarea-10.md con matriz de los 6 patrones oscuros."

## Tasks

- [x] T1 (S1, S2, S3, S5): [Backend] Portabilidad de datos (JSON/CSV), regla difícil de eliminación de cuenta y bloqueo de plan con pendientes (Sub-issue #117) — Commit: 9227226 (PR #121)
- [ ] T2 (S1, S2, S3, S5, S7): [Controlador] Endpoints de exportación de datos, baja de cuenta, resolución de plan y eventos sin PII (Sub-issue #118)
- [ ] T3 (S1, S3, S4, S5, S6): [Interfaz] Centro 'Mi Cuenta y Privacidad', modales de salida honesta (cuenta y plan) y desmantelamiento de patrones oscuros (Sub-issue #119)
- [ ] T4 (S8): [Testing/Docs] Suites PHPUnit y Playwright E2E de salida honesta, y reporte con matriz de 6 patrones en tarea-10.md (Sub-issue #120)

## Log

- L1: "Implementa las subissues de la issue #116, usando gentle-ai y ODD"
- L2: "Sub-issue #117 completada y mergeada (PR #121): portabilidad JSON/CSV, validación de bloqueos de eliminación de cuenta y planes con deudas, inhabilitación con soft deletes y rechazo de invitación."
