# Design: Migraciones, Modelos y Relaciones de Colaboración

## 1. Contexto y Objetivos

El objetivo de este diseño es establecer la base de persistencia y modelos Eloquent para habilitar la colaboración grupal en Planazo (sub-issue #7, Refs #6):
- **Integridad Referencial:** Claves foráneas con eliminación en cascada donde corresponde (`cascadeOnDelete`) y preservación controlada de usuarios (`nullOnDelete` para invitaciones).
- **Regla de Negocio de Voto Único:** Garantizar mediante una restricción `UNIQUE (opcion_id, user_id)` en la base de datos que ningún usuario pueda emitir más de un voto por opción.
- **Relaciones Expresivas:** Definición de navegación intuitiva mediante Eloquent (`$plan->participantes`, `$plan->opciones`, `$opcion->votantes`).

## 2. Esquema Relacional de Base de Datos

```
+--------------------+        +---------------------+        +--------------------+
|       planes       |        |    invitaciones     |        |       users        |
+--------------------+        +---------------------+        +--------------------+
| id (PK)            |<-------| plan_id (FK)        |------->| id (PK)            |
| nombre             |        | email               |        | name               |
| organizador        |        | token (UNIQUE)      |        | email (UNIQUE)     |
| fecha_limite       |        | estado [enum]       |        +--------------------+
| estado             |        | user_id (FK null)   |                   ^
| user_id (FK)       |        +---------------------+                   |
+--------------------+                                                  |
          |                                                             |
          v                                                             |
+--------------------+                                                  |
|      opciones      |                                                  |
+--------------------+                                                  |
| id (PK)            |<------------------+                              |
| plan_id (FK)       |                   |                              |
| user_id (FK)       |-------------------+------------------------------+
| tipo [enum]        |                   |                              |
| valor              |                   |                              |
| descripcion        |                   |                              |
+--------------------+                   |                              |
                                         |                              |
                              +---------------------+                   |
                              |        votos        |                   |
                              +---------------------+                   |
                              | id (PK)             |                   |
                              | opcion_id (FK)      |                   |
                              | user_id (FK)        |-------------------+
                              | UNIQUE(opcion,user) |
                              +---------------------+
```

## 3. Modelos Eloquent y Relaciones

### 3.1 `Invitacion` (`app/Models/Invitacion.php`)
- `plan(): BelongsTo` -> `Plan`
- `user(): BelongsTo` -> `User`
- `scopeAceptadas($query)` -> `where('estado', 'aceptada')`

### 3.2 `Opcion` (`app/Models/Opcion.php`)
- `plan(): BelongsTo` -> `Plan`
- `user(): BelongsTo` -> `User`
- `votos(): HasMany` -> `Voto`
- `votantes(): BelongsToMany` -> `User` through `votos`
- Scopes: `scopeFechas($query)`, `scopeLugares($query)`

### 3.3 `Voto` (`app/Models/Voto.php`)
- `opcion(): BelongsTo` -> `Opcion`
- `user(): BelongsTo` -> `User`

### 3.4 Modificaciones en `Plan` (`app/Models/Plan.php`)
- `opciones(): HasMany` -> `Opcion`
- `invitaciones(): HasMany` -> `Invitacion`
- `participantes(): BelongsToMany` -> `User` a través de tabla `invitaciones` filtrado por `wherePivot('estado', 'aceptada')`

### 3.5 Modificaciones en `User` (`app/Models/User.php`)
- `invitaciones(): HasMany` -> `Invitacion`
- `planesParticipados(): BelongsToMany` -> `Plan`
- `opciones(): HasMany` -> `Opcion`
- `votos(): HasMany` -> `Voto`

## 4. Estrategia de Pruebas Automatizadas

Suite `tests/Unit/ColaboracionModelosTest.php` (PHPUnit):
1. `test_plan_tiene_muchas_opciones_de_fecha_o_lugar`: Valida relación `opciones()` y scopes temáticos.
2. `test_plan_tiene_participantes_a_traves_de_invitaciones_aceptadas`: Valida relación `participantes()` y filtro de pivote.
3. `test_voto_pertenece_a_usuario_y_a_opcion`: Valida relaciones `opcion()`, `user()`, `opcion->votos` y `user->votos`.
4. `test_base_de_datos_impide_voto_duplicado_del_mismo_usuario_por_opcion`: Provoca inserción duplicada y verifica captura de `QueryException`.
5. `test_invitacion_pertenece_a_plan_y_a_usuario`: Valida relación de invitación.
6. `test_opcion_tiene_votantes_a_traves_de_votos`: Valida relación `votantes()` de la opción.
