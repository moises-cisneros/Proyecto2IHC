# Tasks: Endpoint y consulta cronológica de historial con filtros temáticos

## Tareas de Implementación (Refs #15, Refs #1)

- [x] 1. Crear el controlador `app/Http/Controllers/HistorialController.php` con filtrado por `auth()->id()`, orden cronológico y categorías temáticas <!-- id: 1-controlador-historial -->
- [x] 2. Registrar la ruta protegida `GET /historial` en `routes/web.php` bajo middleware `auth` <!-- id: 2-registro-ruta-historial -->
- [x] 3. Crear la vista base `resources/views/planes/historial.blade.php` con chips de filtrado <!-- id: 3-vista-base-historial -->
- [x] 4. Implementar suite de pruebas Feature `tests/Feature/HistorialConsultaTest.php` con al menos 4 casos de prueba <!-- id: 4-suite-pruebas-feature -->
- [x] 5. Validar cumplimiento con script de verificación `scripts/verify-subissue.mjs` <!-- id: 5-verificar-subissue-script -->
