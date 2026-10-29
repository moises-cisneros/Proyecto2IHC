# Tasks: Persistencia y modelo PlanActivity para auditoría de historial

## Tareas de Implementación (Refs #14, Refs #1)

- [x] 1. Crear migración para la tabla `plan_activities` con campos de auditoría y claves foráneas <!-- id: 1-migracion-plan-activities -->
- [x] 2. Crear modelo `PlanActivity` con asignación masiva protegida, relaciones y scope `recientes` <!-- id: 2-modelo-plan-activity -->
- [x] 3. Actualizar modelos `Plan` y `User` con relaciones Eloquent y hooks de auditoría en ciclo de vida <!-- id: 3-relaciones-y-hooks-ciclo-vida -->
- [x] 4. Crear factory `PlanActivityFactory` para generación de registros de prueba <!-- id: 4-factory-plan-activity -->
- [x] 5. Implementar suite de pruebas unitarias `tests/Unit/PlanActivityTest.php` con al menos 4 casos <!-- id: 5-suite-pruebas-unitarias -->
- [x] 6. Validar cumplimiento con script de verificación `scripts/verify-subissue.mjs` <!-- id: 6-validacion-script-subissue -->
