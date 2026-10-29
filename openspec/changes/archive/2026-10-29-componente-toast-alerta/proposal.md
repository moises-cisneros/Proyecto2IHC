# Proposal: Componente Toast Flotante Auto-Dismissable (3s) para Alertas Flash

## Issue Reference
- Sub-issue: Refs #13 (Closes #13)
- Requisito Docente / Madre: Refs #1

## Why
Tras la remoción de SweetAlert2 (que gestionaba tanto diálogos modales como notificaciones flotantes temporales), los mensajes flash de sesión (`session('exito')` y `session('error')`) quedaron relegados a bloques estáticos `<div>` insertados directamente dentro del flujo de la página (`<main class="contenedor">` y `resources/views/layouts/_partials/mensajes.blade.php`).

Esta situación introduce tres problemas críticos de diseño y usabilidad:
1. **Desplazamiento acumulado del diseño (Cumulative Layout Shift - CLS):** Al renderizarse dentro del flujo del documento, las alertas empujan violentamente hacia abajo el encabezado y contenido principal de las vistas, degradando la estabilidad visual de la aplicación.
2. **Falta de descarte automático y persistencia innecesaria:** Las alertas permanecen indefinidamente en pantalla hasta que el usuario navega a otra ruta, ocupando espacio visual valioso una vez consumida la información.
3. **Ausencia de control manual accesible (Nielsen H3 y H1):** No existe un botón de cierre manual con área táctil adecuada (Ley de Fitts $\ge 44$px) ni atributos ARIA (`role="status"`, `aria-live="polite"`) que anuncien el mensaje a tecnologías asistivas sin interrumpir el flujo del usuario.

Para resolver esto y cumplir con los lineamientos de `DESIGN.md`, es indispensable implementar un componente Toast flotante nativo `<x-toast-alerta />` con temporizador de 3000ms, cierre manual accesible y cero disrupción del flujo HTML.

## What Changes
1. **Componente Blade reutilizable:** Crear `resources/views/components/toast-alerta.blade.php` posicionado de forma fija en la esquina superior derecha (`fixed top-5 right-5 z-50`), con fondo semántico según el tipo (`exito` vs `error`), ícono explicativo y botón de cierre `×`.
2. **Temporizador y animación suave:** Configurar auto-dismissable en 3000ms (3 segundos) con transición de opacidad y transformación (`fade-out`), removiendo el nodo del DOM al finalizar.
3. **Control del usuario y ergonomía (Nielsen H3 & Fitts):** Proveer un botón de cierre manual accesible con etiqueta `aria-label="Cerrar notificación"`, dimensiones táctiles mínimas de 44×44px y cursor interactivo.
4. **Función auxiliar de cliente en `public/js/ui.js`:** Exponer `window.mostrarToast(mensaje, tipo)` para permitir que la interfaz dispare notificaciones toast dinámicamente desde scripts del cliente sin recargar la página.
5. **Reemplazo de alertas estáticas en layouts:** Actualizar `resources/views/layouts/_partials/mensajes.blade.php` para renderizar `<x-toast-alerta />` y sincronizar `resources/views/layouts/app.blade.php` y `resources/views/layouts/acceso.blade.php` para erradicar el layout shift.
6. **Pruebas automatizadas de integración:** Desarrollar `tests/Feature/ToastAlertaTest.php` con al menos 4 casos de prueba verificando el renderizado de toasts flash de éxito, error, persistencia de sesión y ausencia de alertas cuando no hay mensajes.

## Capabilities

### New Capabilities
- `toast-notification-system`: Sistema de notificaciones flotantes temporales (3s) y accesibles para mensajes flash y notificaciones dinámicas del cliente sin alterar el flujo visual del documento.

### Modified Capabilities
- `ui-interaction-engine`: Extensión del módulo de interacción en `public/js/ui.js` incorporando la función global `window.mostrarToast()`.

## Impact
- `resources/views/components/toast-alerta.blade.php`: Nuevo componente visual accesible.
- `public/js/ui.js`: Nueva API cliente para feedback de notificaciones.
- `resources/views/layouts/_partials/mensajes.blade.php`: Migración a componente flotante.
- `resources/views/layouts/app.blade.php` y `resources/views/layouts/acceso.blade.php`: Eliminación de saltos de maquetación (CLS cero).
- `tests/Feature/ToastAlertaTest.php`: Cobertura de pruebas automatizadas para mensajes flash.

