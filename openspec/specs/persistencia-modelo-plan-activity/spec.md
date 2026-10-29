# persistencia-modelo-plan-activity Specification

## Purpose
Proveer infraestructura de persistencia y trazabilidad de eventos inmutables para el historial y auditoría de planes en Planazo.

## Requirements

### Requirement: Esquema de base de datos para auditoría de planes
El sistema debe persistir un historial estructurado e inmutable de eventos asociados a planes en la tabla `plan_activities`.

#### Scenario: Creación de tabla y persistencia de registro
- **Given** una instancia de base de datos migrada
- **When** se inserta un registro con `plan_id`, `user_id`, `accion`, `descripcion` y `estado_resultante`
- **Then** el registro se almacena correctamente en `plan_activities`
- **And** mantiene integridad referencial con `planes` y `users`

### Requirement: Modelo Eloquent PlanActivity y relaciones
El modelo `PlanActivity` debe definir asignación masiva protegida y relaciones con los modelos `Plan` y `User`.

#### Scenario: Acceso a relaciones y navegación de modelos
- **Given** una actividad persistida en la base de datos
- **When** se accede a `$actividad->plan` y `$actividad->user`
- **Then** se obtienen las instancias correspondientes de `Plan` y `User`
- **And** `$plan->actividades` y `$user->actividades` devuelven la colección asociada

### Requirement: Hooks de ciclo de vida automáticos en Plan
El modelo `Plan` debe registrar automáticamente eventos de auditoría ante su creación, actualización y transición de estado.

#### Scenario: Creación de un nuevo plan
- **Given** un usuario autenticado o especificado
- **When** se invoca `Plan::create(...)`
- **Then** se inserta automáticamente una actividad con `accion = 'creacion'`

#### Scenario: Mutación de estado de un plan
- **Given** un plan existente en estado `pendiente`
- **When** se modifica su estado a `confirmado` y se guarda
- **Then** se inserta automáticamente una actividad con `accion = 'cambio_estado'` y `estado_resultante = 'confirmado'`

### Requirement: Scope de ordenación cronológica descendente
El modelo `PlanActivity` debe proveer el scope `scopeRecientes` para ordenar registros del más reciente al más antiguo.

#### Scenario: Consulta con scope recientes
- **Given** múltiples actividades registradas en diferentes momentos
- **When** se ejecuta `PlanActivity::recientes()->get()`
- **Then** la colección resultante se ordena cronológicamente de forma descendente
