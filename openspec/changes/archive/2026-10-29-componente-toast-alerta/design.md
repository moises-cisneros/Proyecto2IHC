# Design: Componente Toast Flotante Auto-Dismissable (3s) para Alertas Flash

## 1. Contexto y Objetivos

El objetivo de este diseño es resolver la deuda técnica heredada tras la eliminación de SweetAlert2, implementando un componente Toast flotante accesible `<x-toast-alerta />` para los mensajes flash de sesión (`exito` y `error`), auto-ocultable en 3 segundos y con botón de cierre manual, garantizando:
- Cero desplazamiento acumulativo del diseño (CLS = 0).
- Cumplimiento de la Ley de Fitts ($\ge 44$px de área táctil para el botón de cierre).
- Salida de emergencia y control del usuario (Nielsen H3).
- Feedback del estado del sistema accesible (Nielsen H1 y WAI-ARIA `role="status"` / `role="alert"`).

## 2. Arquitectura de Componentes

### 2.1 Componente Blade: `resources/views/components/toast-alerta.blade.php`

El componente se ubicará de forma fija y flotante en la esquina superior derecha del viewport:
```html
<div id="toast-contenedor"
     class="fixed top-5 right-5 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none"
     aria-live="polite">
    {{-- Notificación Toast individual --}}
    <div id="toast-alerta"
         role="{{ $tipo === 'error' ? 'alert' : 'status' }}"
         class="pointer-events-auto flex items-center justify-between p-4 rounded-xl border shadow-md transition-all duration-300 ease-out {{ $clasesContenedor }}">
        <div class="flex items-center gap-3">
            {{-- Ícono SVG según tipo --}}
            <span>{{ $mensaje }}</span>
        </div>
        {{-- Botón de descarte manual (Fitts >= 44x44px) --}}
        <button type="button"
                id="toast-btn-cerrar"
                class="min-w-[44px] min-h-[44px] flex items-center justify-center text-lg leading-none rounded-lg hover:bg-black/5"
                aria-label="Cerrar notificación">
            &times;
        </button>
    </div>
</div>
```

Tokens visuales alineados con `DESIGN.md`:
- **Éxito (`exito`):** Fondo `#edf7ef`, borde `#c2e2c8`, texto `#25663a`, ícono SVG de check.
- **Error (`error`):** Fondo `#fbeceb`, borde `#f5c6cb`, texto `#c0392b`, ícono SVG de alerta.

### 2.2 Ciclo de Vida y Temporizador en JavaScript

El componente incluirá un script embebido liviano o manejado por `public/js/ui.js` que:
1. Detecta la presencia de `#toast-alerta`.
2. Establece un `setTimeout` de 3000ms.
3. Al cumplirse el tiempo o al hacer clic en `#toast-btn-cerrar`:
   - Agrega clases de desvanecimiento (`opacity-0 translate-y-[-10px]` o `fade-out`).
   - Espera la duración de la transición CSS (300ms) y ejecuta `.remove()` en el elemento.

### 2.3 API Global en `public/js/ui.js`: `window.mostrarToast(mensaje, tipo)`

Permite despachar toasts dinámicos en tiempo de ejecución:
```javascript
window.mostrarToast = function (mensaje, tipo = 'exito') {
    // Busca o crea #toast-contenedor en document.body
    // Inyecta el markup del toast con su ícono y botón de cierre
    // Configura el auto-dismiss a los 3000ms y el handler de clic en el botón cerrar
};
```

### 2.4 Unificación en Layouts

Se actualizará `resources/views/layouts/_partials/mensajes.blade.php`:
```blade
@if (session('exito'))
    <x-toast-alerta tipo="exito" :mensaje="session('exito')" />
@endif

@if (session('error'))
    <x-toast-alerta tipo="error" :mensaje="session('error')" />
@endif
```
Y se incluirá de forma consistente en:
- `resources/views/layouts/app.blade.php` (reemplazando las alertas estáticas dentro de `<main class="contenedor">`).
- `resources/views/layouts/acceso.blade.php` (reemplazando las alertas estáticas dentro de `.tarjeta-acceso`).

## 3. Estrategia de Pruebas (PHPUnit)

Se creará la clase de prueba `tests/Feature/ToastAlertaTest.php` extendiendo `Tests\TestCase` con al menos 4 métodos:
1. `test_renderiza_toast_exito_cuando_hay_mensaje_en_sesion`: Simula una sesión con `exito` y verifica la presencia del componente `<x-toast-alerta>` o su estructura en el DOM.
2. `test_renderiza_toast_error_cuando_hay_mensaje_en_sesion`: Simula una sesión con `error` y valida el rol `alert` y clases semánticas de error.
3. `test_no_renderiza_toast_cuando_la_sesion_esta_vacia`: Comprueba que sin mensajes flash no se renderice el toast.
4. `test_toast_contiene_boton_de_cierre_accesible`: Verifica que el HTML emitido contenga el botón con `aria-label="Cerrar notificación"`.

