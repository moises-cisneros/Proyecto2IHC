# Tasks: Componente Toast Flotante Auto-Dismissable (3s) para Alertas Flash

## Tareas de Implementación (Refs #13, Refs #1)

- [x] 1. Crear el componente accesible `resources/views/components/toast-alerta.blade.php` con ubicación flotante (`fixed top-5 right-5 z-50`), auto-dismiss a los 3s y botón de cierre accesible <!-- id: 1-crear-componente-toast-alerta -->
- [x] 2. Implementar y exponer la función auxiliar `window.mostrarToast(mensaje, tipo)` en `public/js/ui.js` para notificaciones dinámicas de cliente <!-- id: 2-exponer-mostrar-toast-en-ui-js -->
- [x] 3. Actualizar `resources/views/layouts/_partials/mensajes.blade.php` para renderizar `<x-toast-alerta />` y sincronizar `resources/views/layouts/app.blade.php` y `resources/views/layouts/acceso.blade.php` eliminando las alertas estáticas <!-- id: 3-unificar-layouts-parcial-mensajes -->
- [x] 4. Crear suite de pruebas de integración `tests/Feature/ToastAlertaTest.php` con al menos 4 casos de prueba cubriendo éxito, error, ausencia de flash y atributos de accesibilidad <!-- id: 4-crear-tests-feature-toast-alerta -->
- [x] 5. Validar suite completa de pruebas y criterios de políticas con `scripts/verify-subissue.mjs` <!-- id: 5-validar-tests-y-suite -->

