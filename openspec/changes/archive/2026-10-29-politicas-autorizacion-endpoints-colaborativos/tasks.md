# Tareas: Políticas de autorización y endpoints colaborativos

- [x] 1. Crear la política de autorización `app/Policies/PlanPolicy.php` <!-- id: task-1-policy -->
- [x] 2. Registrar la política en `app/Providers/AppServiceProvider.php` si es necesario <!-- id: task-2-provider -->
- [x] 3. Implementar `app/Http/Controllers/ColaboracionController.php` con métodos `invitar`, `guardarOpcion` y `votar` <!-- id: task-3-controller -->
- [x] 4. Registrar rutas POST `/planes/{plan}/invitar`, `/planes/{plan}/opciones` y `/opciones/{opcion}/votar` en `routes/web.php` <!-- id: task-4-routes -->
- [x] 5. Aplicar autorización `PlanPolicy` a los métodos de mutación en `app/Http/Controllers/PlanesController.php` <!-- id: task-5-authorize-planes -->
- [x] 6. Crear la suite de pruebas funcionales `tests/Feature/ColaboracionEndpointsTest.php` con al menos 4 casos <!-- id: task-6-tests -->
- [x] 7. Ejecutar y verificar todas las pruebas automatizadas en Docker (`php artisan test`) <!-- id: task-7-verify -->
