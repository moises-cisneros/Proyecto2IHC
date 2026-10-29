# Tasks: Vista Blade de historial con tarjetas ActivityCard y filtros accesibles

## Tareas de Implementación (Refs #16, Refs #1)

- [x] 1. Extraer y modularizar la barra de navegación en `resources/views/layouts/_partials/nav.blade.php` incorporando enlace accesible a Historial (Fitts >= 44px) <!-- id: 1-modularizar-nav-historial -->
- [x] 2. Crear el componente Blade `resources/views/components/activity-card.blade.php` con las 4 dimensiones de interacción explícitas <!-- id: 2-componente-activity-card -->
- [x] 3. Diseñar la vista `resources/views/planes/historial.blade.php` con selector temático de n=4 chips de Hick y estado vacío ilustrado <!-- id: 3-vista-planes-historial -->
- [x] 4. Implementar suite de pruebas Feature `tests/Feature/HistorialVistaTest.php` con al menos 4 casos de prueba <!-- id: 4-suite-pruebas-vista-feature -->
- [x] 5. Validar cumplimiento con script de verificación `scripts/verify-subissue.mjs` <!-- id: 5-verificar-subissue-script -->
