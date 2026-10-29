# Tareas: Salida de emergencia y abandono de plan

- [x] 1. Agregar habilidad `abandonar` en `app/Policies/PlanPolicy.php` <!-- id: task-1-policy -->
- [x] 2. Implementar método `abandonar` en `app/Http/Controllers/ColaboracionController.php` <!-- id: task-2-controller -->
- [x] 3. Registrar ruta `POST /planes/{plan}/abandonar` en `routes/web.php` <!-- id: task-3-route -->
- [x] 4. Agregar botón accesible y modal `<dialog id="modal-abandonar">` en `resources/views/planes/_cabecera.blade.php` <!-- id: task-4-blade -->
- [x] 5. Crear la suite de pruebas `tests/Feature/AbandonoPlanTest.php` con al menos 4 casos <!-- id: task-5-tests -->
- [x] 6. Ejecutar suite de pruebas completa en Docker y verificar con `verify-subissue.mjs 10` <!-- id: task-6-verify -->
