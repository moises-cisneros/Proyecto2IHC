# Proposal: Reemplazo Integral de SweetAlert2 por Componente Modal <dialog> Nativo

## Issue Reference
- Sub-issue: Refs #3 (Closes #3)
- Requisito Docente / Madre: Refs #1

## Why
El proyecto recibido carga librerías externas (SweetAlert2 y NProgress) desde CDNs públicos en `resources/views/layouts/app.blade.php` e introduce una demora artificial forzada de 1 segundo (`const TIEMPO_MINIMO = 1000;`) en `public/js/ui.js` antes de procesar envíos de formularios. Esto genera lentitud injustificada (fricción de usabilidad contra Nielsen H1), riesgo de rotura sin conexión a internet en entornos Docker o evaluación docente offline, y estilos inconsistentes con la identidad visual definida en `DESIGN.md`.

Para cumplir con las directivas docentes, la Ley de Fitts y los principios de Interacción Humano-Computador (IHC), es necesario reemplazar SweetAlert2 y NProgress por el elemento HTML5 nativo `<dialog>`, eliminar los retardos artificiales y estandarizar las confirmaciones modales con un componente Blade reutilizable y accesible.

## What Changes
1. **Limpieza de dependencias externas:** Eliminar las etiquetas `<link>` y `<script>` de SweetAlert2 y NProgress en `resources/views/layouts/app.blade.php`.
2. **Componente modal nativo:** Crear `resources/views/components/dialog-confirm.blade.php` utilizando `<dialog>`, backdrop nativo (`rgba(0, 0, 0, 0.4)`), área táctil mínima de 44×44px (Ley de Fitts) y separación mínima de 12px (`gap-3`) entre acciones antagónicas.
3. **Optimización de respuesta en JavaScript:** Refactorizar `public/js/ui.js` eliminando `TIEMPO_MINIMO = 1000`, quitando llamadas a `Swal` y `NProgress`, y controlando el `<dialog>` nativo de forma inmediata y accesible.
4. **Desambiguación léxica en confirmaciones:** Actualizar los botones y formularios de cambio de estado (confirmar, cancelar) y eliminación de planes en `resources/views/planes/listar.blade.php` para invocar el `<dialog>` nativo con textos explícitos (Ley de Hick: "Mantener plan" vs "Sí, cancelar").

## Capabilities

### New Capabilities
- `native-modal-dialog`: Soporte de confirmaciones modales accesibles y ligeras mediante el elemento nativo `<dialog>` de HTML5 sin librerías externas.

### Modified Capabilities
- `ui-interaction-engine`: Supresión de demoras forzadas en `public/js/ui.js`, garantizando respuesta instantánea del sistema a eventos de usuario.

## Impact
- `resources/views/layouts/app.blade.php`: Cero dependencias de CDNs externos; operación 100% offline.
- `resources/views/components/dialog-confirm.blade.php`: Nuevo componente Blade reutilizable.
- `public/js/ui.js`: Reducción de complejidad y eliminación de librerías pesadas en el cliente.
- `resources/views/planes/listar.blade.php`: Confirmaciones coherentes y ergonómicas.

