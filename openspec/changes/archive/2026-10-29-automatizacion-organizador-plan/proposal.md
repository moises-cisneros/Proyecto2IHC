# Proposal: Automatización del Organizador por Sesión y Badge de Solo Lectura

## Issue Reference
- Sub-issue: Refs #4 (Closes #4)
- Requisito Docente / Madre: Refs #1

## Why
El formulario de creación de planes (`resources/views/planes/listar.blade.php`) exige actualmente ingresar manualmente el nombre del organizador mediante un campo editable `<input name="organizador">`, a pesar de que el usuario ya se encuentra debidamente autenticado en la sesión de Laravel (`auth()->user()->name`).

Esta redundancia introduce:
1. **Fricción cognitiva y violación de usabilidad:** Contraviene la heurística de Nielsen H6 (*Reconocimiento antes que recuerdo*) al obligar al usuario a tipear su propio nombre de memoria, y Nielsen H8 (*Diseño estético y minimalista*) al sobrecargar el formulario con entradas innecesarias.
2. **Inconsistencia de datos:** Permite que un usuario autenticado escriba cualquier nombre arbitrario como organizador de un plan que le pertenece (`user_id = auth()->id()`), generando discrepancias de auditoría y trazabilidad en el dominio.

Para resolver esto y dar cumplimiento a los criterios de la sub-issue #4 y las directrices de `DESIGN.md`:
- Se elimina el campo editable `<input name="organizador">` en el modal de creación.
- Se presenta en su lugar un badge informativo de solo lectura con el nombre del usuario autenticado: *«Organizador: {{ auth()->user()->name }}»*.
- En el backend (`PlanesController::guardar`), se asigna automáticamente `$plan->organizador = auth()->user()->name;` y se elimina la validación obligatoria del payload de la petición HTTP.
- Se mantiene intacta la columna `organizador` en la tabla `planes` para preservar el esquema de base de datos recibido.

## What Changes
1. **Modularización del modal de creación:** Extraer el `<dialog id="modal-crear">` a un parcial reutilizable `resources/views/planes/_modal_crear.blade.php` e incluirlo con `@include('planes._modal_crear')` en `listar.blade.php`.
2. **Badge informativo de solo lectura:** Reemplazar el bloque de input por un componente badge de texto atenuado (`#6f6f6a`) con icono de usuario y valor no editable `auth()->user()->name`.
3. **Automatización en el controlador:** En `PlanesController::guardar`, remover `'organizador' => 'required|max:255'` del validador y asignar directamente `$plan->organizador = auth()->user()->name;`.
4. **Preservación de persistencia:** Mantener la columna `organizador` en la base de datos sin alterar migraciones preexistentes.
5. **Cobertura con pruebas automatizadas:** Crear `tests/Feature/CreacionPlanTest.php` con al menos 4 casos de prueba para validar el guardado automático del organizador, restricciones de validación y protección de acceso middleware `auth`.

## Capabilities

### Modified Capabilities
- `plan-creation-workflow`: El flujo de alta de planes ya no solicita el organizador al usuario; lo infiere de manera íntegra y segura a partir de la sesión autenticada.

## Impact
- `resources/views/planes/listar.blade.php`: Simplificación de la plantilla delegando el modal al parcial `_modal_crear.blade.php`.
- `resources/views/planes/_modal_crear.blade.php`: Nuevo parcial con badge de solo lectura según `DESIGN.md`.
- `app/Http/Controllers/PlanesController.php`: Validación y asignación automática del organizador.
- `tests/Feature/CreacionPlanTest.php`: Nueva suite con pruebas automatizadas de integración.

