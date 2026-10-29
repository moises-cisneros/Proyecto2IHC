# Tasks: Automatización del Organizador por Sesión y Badge de Solo Lectura

## Tareas de Implementación (Refs #4, Refs #1)

- [x] 1. Modularizar el modal de creación extrayéndolo a `resources/views/planes/_modal_crear.blade.php` e incluirlo en `resources/views/planes/listar.blade.php` <!-- id: 1-modularizar-modal-crear -->
- [x] 2. Sustituir el `<input name="organizador">` en `_modal_crear.blade.php` por el badge informativo de solo lectura según DESIGN.md <!-- id: 2-badge-organizador-modal -->
- [x] 3. Refactorizar `PlanesController::guardar` para suprimir la validación de `organizador` y asignar automáticamente `$plan->organizador = auth()->user()->name` <!-- id: 3-controlador-asignacion-automatica -->
- [x] 4. Crear la suite de pruebas automatizadas `tests/Feature/CreacionPlanTest.php` con al menos 4 casos de prueba <!-- id: 4-tests-creacion-plan -->
- [x] 5. Ejecutar la suite completa de pruebas en Docker y validar con `verify-subissue.mjs` <!-- id: 5-validar-tests-y-suite -->

