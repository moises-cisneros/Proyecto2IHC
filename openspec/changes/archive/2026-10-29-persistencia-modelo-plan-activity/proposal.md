# Proposal: Persistencia y modelo PlanActivity para auditoría de historial

## Issue Reference
- Sub-issue: Refs #14 (Closes #14)
- Requisito Docente / Madre: Refs #1

## Why
El sistema Planazo requiere registrar de forma inmutable el historial de acciones y cambios de estado sobre los planes (auditoría e historial). En el estado heredado del proyecto no existía infraestructura de persistencia ni modelos que capturaran los eventos de creación, actualización o transición de estado en las entidades de dominio.

La sub-issue #14 aborda la capa de persistencia y backend creando la tabla `plan_activities`, el modelo `PlanActivity`, sus relaciones Eloquent con `Plan` y `User`, hooks de ciclo de vida automáticos y una suite de pruebas unitarias que blinde esta funcionalidad.

## What Changes
1. **Migración de Base de Datos (`database/migrations/2026_10_29_100000_create_plan_activities_table.php`):**
   - Creación de la tabla `plan_activities` con campos: `id`, `plan_id` (FK a `planes`), `user_id` (FK a `users`, nullable con nullOnDelete), `accion`, `descripcion`, `estado_resultante` (nullable) y marcas de tiempo (`timestamps`).
2. **Modelo Eloquent (`app/Models/PlanActivity.php`):**
   - Configuración de asignación masiva protegida (`$fillable`).
   - Definición de relaciones inversas `plan()` y `user()`.
   - Implementación del scope de consulta `scopeRecientes` para ordenación cronológica descendente.
3. **Integración con Modelos Existentes:**
   - En `app/Models/Plan.php`: Relación `actividades(): HasMany` y hooks en `booted()` (`created`, `updated`) para registrar auditoría automáticamente ante eventos de creación, actualización y cambio de estado.
   - En `app/Models/User.php`: Relación `actividades(): HasMany`.
4. **Factory:**
   - Creación de `database/factories/PlanActivityFactory.php`.
5. **Suite de Pruebas Unitarias (`tests/Unit/PlanActivityTest.php`):**
   - Implementación de 5 casos de prueba unitarios cubriendo persistencia, relaciones, hooks automáticos de ciclo de vida y ordenación por scope.

## Capabilities

### New Capabilities
- `plan-activity-audit`: Persistencia inmutable del historial de eventos y transiciones de estado sobre planes.

## Impact
- `database/migrations/2026_10_29_100000_create_plan_activities_table.php`
- `app/Models/PlanActivity.php`
- `app/Models/Plan.php`
- `app/Models/User.php`
- `database/factories/PlanActivityFactory.php`
- `tests/Unit/PlanActivityTest.php`
