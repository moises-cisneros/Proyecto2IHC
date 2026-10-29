# Design: Persistencia y Modelo PlanActivity para Auditoría de Historial

## 1. Contexto y Objetivos

El objetivo de este diseño es saldar la deuda técnica de auditoría de historial en Planazo (sub-issue #14, Refs #1) garantizando persistencia inmutable, trazabilidad de eventos sobre los planes y acoplamiento limpio mediante eventos del ciclo de vida de Eloquent.

Principios arquitectónicos:
- **Inmutabilidad:** Cada registro de `PlanActivity` representa un evento ocurrido en el pasado.
- **Trazabilidad:** Asociación clara con el plan intervenido (`plan_id`) y con el actor (`user_id`).
- **Automatización desacoplada:** El modelo `Plan` registra sus propias transiciones sin ensuciar la lógica de negocio de los controladores.

## 2. Esquema de Datos y Persistencia

### Tabla: `plan_activities`

| Columna | Tipo | Restricciones / Índices | Propósito |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK, Auto Increment | Identificador único del evento |
| `plan_id` | BIGINT UNSIGNED | FK `planes.id`, ON DELETE CASCADE | Plan al que pertenece el evento |
| `user_id` | BIGINT UNSIGNED | FK `users.id`, NULLABLE, ON DELETE SET NULL | Usuario actor del evento |
| `accion` | VARCHAR(255) | NOT NULL | Tipo de acción (`creacion`, `actualizacion`, `cambio_estado`) |
| `descripcion` | TEXT | NOT NULL | Resumen legible del evento |
| `estado_resultante`| VARCHAR(255) | NULLABLE | Estado del plan tras la acción (`pendiente`, `confirmado`, `cancelado`) |
| `created_at` | TIMESTAMP | NULLABLE | Momento de registro del evento |
| `updated_at` | TIMESTAMP | NULLABLE | Momento de actualización técnica |

## 3. Modelo Eloquent y Relaciones

### 3.1 `App\Models\PlanActivity`
- `$fillable = ['plan_id', 'user_id', 'accion', 'descripcion', 'estado_resultante']`
- Relaciones:
  - `plan(): BelongsTo` -> `App\Models\Plan`
  - `user(): BelongsTo` -> `App\Models\User`
- Scopes:
  - `scopeRecientes($query)`: `orderBy('created_at', 'desc')->orderBy('id', 'desc')`

### 3.2 Modificaciones en `App\Models\Plan`
- Relación: `actividades(): HasMany`
- Hooks en `booted()`:
  - `created`: Crea actividad con `accion = 'creacion'`, `estado_resultante = $plan->estado`.
  - `updated`: Si `wasChanged('estado')` registra `accion = 'cambio_estado'`, de lo contrario `accion = 'actualizacion'`.

### 3.3 Modificaciones en `App\Models\User`
- Relación: `actividades(): HasMany`

## 4. Estrategia de Pruebas Automatizadas

Suite `tests/Unit/PlanActivityTest.php` ejecutada sobre SQLite en memoria:
1. `test_creacion_de_actividad_con_datos_validos_persiste_en_bd`: Aserción directa en tabla `plan_activities`.
2. `test_actividad_pertenece_a_plan_y_a_usuario`: Validación de relaciones bidireccionales `plan`, `user`, `plan->actividades` y `user->actividades`.
3. `test_cambio_de_estado_de_plan_genera_registro_en_historial`: Verificación del hook automático al mutar `$plan->estado`.
4. `test_scope_recientes_ordena_cronologicamente_descendente`: Comprobación del orden cronológico inverso.
5. `test_creacion_de_plan_registra_evento_en_historial_automaticamente`: Verificación del hook en creación.
