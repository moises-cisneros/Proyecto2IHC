# Tasks: Migraciones, modelos y relaciones de colaboración

## Tareas de Implementación (Refs #7, Refs #6)

- [x] 1. Crear migración y modelo para `invitaciones` con tokens únicos y estados <!-- id: 1-migracion-modelo-invitaciones -->
- [x] 2. Crear migración y modelo para `opciones` con tipos fecha y lugar <!-- id: 2-migracion-modelo-opciones -->
- [x] 3. Crear migración y modelo para `votos` con restricción UNIQUE en (opcion_id, user_id) <!-- id: 3-migracion-modelo-votos -->
- [x] 4. Configurar relaciones Eloquent en `Plan`, `User`, `Invitacion`, `Opcion` y `Voto` <!-- id: 4-relaciones-eloquent-colaboracion -->
- [x] 5. Crear factories de prueba para `Invitacion`, `Opcion` y `Voto` <!-- id: 5-factories-colaboracion -->
- [x] 6. Implementar suite de pruebas unitarias `tests/Unit/ColaboracionModelosTest.php` con al menos 4 casos de prueba <!-- id: 6-suite-pruebas-unitarias-colaboracion -->
- [x] 7. Validar cumplimiento con script de verificación `scripts/verify-subissue.mjs 7` <!-- id: 7-verificar-subissue-script -->
