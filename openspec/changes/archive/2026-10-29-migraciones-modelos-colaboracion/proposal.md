# Proposal: Migraciones, modelos y relaciones de colaboración

## Issue Reference
- Sub-issue: Refs #7 (Closes #7)
- Requisito Docente / Madre: Refs #6

## Why
El sistema Planazo avanza en la implementación de la Tarea 5 ("Invitar participantes, proponer fechas o lugares, votar por una opción"). En la arquitectura heredada, el modelo `Plan` era unipersonal (un único `user_id` propietario) sin soporte para invitaciones, propuestas alternativas ni votaciones colaborativas.

La sub-issue #7 crea los cimientos de la capa de persistencia y modelos Eloquent para la colaboración:
1. Tabla y modelo `invitaciones` con tokens únicos y estados (`pendiente`, `aceptada`, `rechazada`).
2. Tabla y modelo `opciones` para registrar propuestas de fecha o lugar vinculadas a planes y usuarios.
3. Tabla y modelo `votos` con restricción de clave única compuesta (`['opcion_id', 'user_id']`) que previene a nivel de base de datos votos duplicados del mismo usuario por opción.
4. Relaciones Eloquent completas en `Plan`, `User`, `Invitacion`, `Opcion` y `Voto`.

## What Changes
1. **Migraciones de Base de Datos:**
   - `database/migrations/2026_10_29_120000_create_invitaciones_table.php`: Define `id`, `plan_id` (FK cascade), `email`, `token` (unique), `estado` enum, `user_id` (FK nullable nullOnDelete) y `timestamps`.
   - `database/migrations/2026_10_29_120001_create_opciones_table.php`: Define `id`, `plan_id` (FK cascade), `user_id` (FK cascade), `tipo` enum ('fecha', 'lugar'), `valor`, `descripcion` nullable y `timestamps`.
   - `database/migrations/2026_10_29_120002_create_votos_table.php`: Define `id`, `opcion_id` (FK cascade), `user_id` (FK cascade), `timestamps` y `$table->unique(['opcion_id', 'user_id'])`.
2. **Modelos Eloquent y Relaciones:**
   - `app/Models/Invitacion.php`: `$fillable`, relaciones `plan()`, `user()` y scope `scopeAceptadas()`.
   - `app/Models/Opcion.php`: `$fillable`, relaciones `plan()`, `user()`, `votos()`, `votantes()` y scopes `scopeFechas()`, `scopeLugares()`.
   - `app/Models/Voto.php`: `$fillable`, relaciones `opcion()` y `user()`.
   - `app/Models/Plan.php`: Relaciones `opciones(): HasMany`, `invitaciones(): HasMany` y `participantes(): BelongsToMany` (a través de invitaciones con estado aceptada).
   - `app/Models/User.php`: Relaciones `invitaciones(): HasMany`, `planesParticipados(): BelongsToMany`, `opciones(): HasMany` y `votos(): HasMany`.
3. **Factories de Prueba:**
   - `database/factories/InvitacionFactory.php`
   - `database/factories/OpcionFactory.php`
   - `database/factories/VotoFactory.php`
4. **Suite de Pruebas Unitarias (`tests/Unit/ColaboracionModelosTest.php`):**
   - 6 casos de prueba unitarios validando persistencia, relaciones cruzadas, participantes por invitación aceptada y restricción única de voto en base de datos.

## Capabilities

### New Capabilities
- `collaboration-persistence`: Esquema relacional y modelos para invitaciones, propuestas de fechas/lugares y votación con unicidad a nivel de esquema.

## Impact
- `database/migrations/2026_10_29_120000_create_invitaciones_table.php`
- `database/migrations/2026_10_29_120001_create_opciones_table.php`
- `database/migrations/2026_10_29_120002_create_votos_table.php`
- `app/Models/Invitacion.php`
- `app/Models/Opcion.php`
- `app/Models/Voto.php`
- `app/Models/Plan.php`
- `app/Models/User.php`
- `database/factories/InvitacionFactory.php`
- `database/factories/OpcionFactory.php`
- `database/factories/VotoFactory.php`
- `tests/Unit/ColaboracionModelosTest.php`
