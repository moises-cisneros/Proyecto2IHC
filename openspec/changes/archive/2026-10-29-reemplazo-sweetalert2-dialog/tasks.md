# Tasks: Reemplazo Integral de SweetAlert2 por Componente Modal <dialog> Nativo

## Tareas de Implementación (Refs #3, Refs #1)

- [x] 1. Remover etiquetas CDN de SweetAlert2 y NProgress en `resources/views/layouts/app.blade.php` <!-- id: 1-remover-cdns-app-blade -->
- [x] 2. Crear el componente accesible `resources/views/components/dialog-confirm.blade.php` con backdrop nativo y botones de mínimo 44×44px <!-- id: 2-crear-componente-dialog-confirm -->
- [x] 3. Refactorizar `public/js/ui.js` para suprimir la demora artificial de 1000ms y orquestar el diálogo nativo sin dependencias de `Swal` o `NProgress` <!-- id: 3-refactorizar-ui-js -->
- [x] 4. Actualizar los flujos y textos de confirmación (confirmar, cancelar, eliminar) en `resources/views/planes/listar.blade.php` <!-- id: 4-actualizar-flujos-confirmacion -->
- [x] 5. Corregir aserción en `tests/Feature/ExampleTest.php` y validar suite completa de pruebas con `verify-subissue.mjs` <!-- id: 5-validar-tests-y-suite -->
