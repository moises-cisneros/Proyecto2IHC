# Proposal: Endpoint y consulta cronológica de historial con filtros temáticos

## Issue Reference
- Sub-issue: Refs #15 (Closes #15)
- Requisito Docente / Madre: Refs #1

## Why
El sistema Planazo cuenta con persistencia de historial de actividad mediante la tabla `plan_activities` y el modelo `PlanActivity`. Sin embargo, faltaba la capa de controlador y rutas web que expusiera la consulta de estos registros con aislamiento estricto por usuario autenticado (`auth()->id()`), orden cronológico descendente y filtros temáticos basados en la Ley de Hick ($n=4$).

La sub-issue #15 resuelve esta necesidad implementando el controlador `HistorialController`, el endpoint `GET /historial` bajo el middleware de autenticación `auth`, soporte para filtros temáticos (`todos`, `estados`, `creaciones`, `participacion`) y una suite integral de pruebas Feature (`tests/Feature/HistorialConsultaTest.php`).

## What Changes
1. **Controlador (`app/Http/Controllers/HistorialController.php`):**
   - Implementación del método `index(Request $request)` que filtra actividades exclusivamente para `auth()->id()`.
   - Consulta eager loading de la relación `plan` (`PlanActivity::with('plan')`).
   - Aplicación de filtros temáticos por categoría:
     - `todos`: Colección completa del usuario.
     - `estados`: Acciones de cambio de estado (`cambio_estado`).
     - `creaciones`: Acciones de creación (`creacion`).
     - `participacion`: Acciones colaborativas (`invitacion`, `voto`, `propuesta`, `participacion`).
   - Orden cronológico descendente mediante `latest()`.
   - Respuesta dual: renderizado de vista Blade `planes.historial` o respuesta JSON en caso de peticiones API.
2. **Rutas Web (`routes/web.php`):**
   - Registro de la ruta `GET /historial` vinculada a `HistorialController::class, 'index'` con nombre de ruta `historial.index` dentro del grupo protegido por middleware `auth`.
3. **Vista Base (`resources/views/planes/historial.blade.php`):**
   - Estructuración de la plantilla base extendiendo `layouts.app` con chips de filtrado temático y renderizado de tarjetas de actividad.
4. **Suite de Pruebas Feature (`tests/Feature/HistorialConsultaTest.php`):**
   - 6 casos de prueba automatizados verificando intercepción de invitados, aislamiento estricto entre usuarios, filtros por categoría y orden cronológico descendente.

## Capabilities

### New Capabilities
- `history-query-endpoint`: Consulta paginada y filtrable del historial de actividades personales de un usuario autenticado.

## Impact
- `app/Http/Controllers/HistorialController.php`
- `routes/web.php`
- `resources/views/planes/historial.blade.php`
- `tests/Feature/HistorialConsultaTest.php`
