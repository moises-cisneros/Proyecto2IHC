# Design: Reemplazo Integral de SweetAlert2 por Componente Modal <dialog> Nativo

## Contexto y Motivación
El diagnóstico arquitectónico (`docs/internal/InformeTarea5.md`, Decisión 1) determinó que SweetAlert2 y NProgress introducen fragilidad por conexión a CDNs externos y generan una experiencia lenta e inconsistente debido a la espera artificial de 1000ms configurada en `public/js/ui.js`. Además, el navegador web moderno ya proporciona la API nativa de `<dialog>`, permitiendo accesibilidad por teclado nativa (soporte automático de la tecla Escape, atrapamiento de foco y `showModal()`).

## Decisiones Técnicas

### 1. Desmantelamiento de CDNs en `layouts/app.blade.php`
- Se remueven:
  - `<link rel="stylesheet" href="https://unpkg.com/nprogress@0.2.0/nprogress.css">`
  - `<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">`
  - `<script src="https://unpkg.com/nprogress@0.2.0/nprogress.js"></script>`
  - `<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>`
- Se inyecta el componente Blade `<x-dialog-confirm />` en el layout principal para que esté disponible globalmente para cualquier vista que requiera confirmación preventiva.

### 2. Componente `resources/views/components/dialog-confirm.blade.php`
- Estructura semántica:
  ```blade
  <dialog id="{{ $id }}" class="dialogo-modal" aria-labelledby="{{ $id }}-titulo" aria-describedby="{{ $id }}-mensaje">
      <div class="dialogo-contenido">
          <h2 id="{{ $id }}-titulo" class="dialogo-titulo">{{ $title }}</h2>
          <p id="{{ $id }}-mensaje" class="dialogo-mensaje">{{ $message }}</p>
          <div class="dialogo-botones">
              <button type="button" id="{{ $id }}-cancelar" class="boton boton-claro min-h-[44px]">
                  {{ $cancelText }}
              </button>
              <button type="button" id="{{ $id }}-aceptar" class="boton min-h-[44px]">
                  {{ $confirmText }}
              </button>
          </div>
      </div>
  </dialog>
  ```
- Ergonomía y Tokens (`DESIGN.md`):
  - Fondo blanco `#ffffff`, radio `12px` (`rounded-xl`), padding `24px`.
  - Backdrop nativo `rgba(0, 0, 0, 0.4)`.
  - Botones con altura táctil mínima de 44px (Ley de Fitts).
  - Separación antagónica de al menos 12px (`gap-3`) entre cancelar y confirmar.

### 3. Refactorización de `public/js/ui.js`
- Eliminación total de `TIEMPO_MINIMO = 1000;`, `esperar()`, objetos `Swal`, `Toast` y `NProgress`.
- Manejador de eventos delegado para elementos con atributos `data-confirm`:
  - Lee `data-confirm` (mensaje), `data-title` (título del diálogo), `data-btn-ok` (texto confirmar), `data-btn-cancel` (texto cancelar) y `data-type` (peligro/destructivo para estilizar el botón con `.boton-rojo`).
  - Abre el diálogo usando `dialog.showModal()`.
  - Si el usuario pulsa cancelar o la tecla `Escape`, se cierra el modal sin alterar el estado.
  - Si el usuario confirma, se cierra el diálogo y se envía inmediatamente el formulario contenedor (`form.submit()`) o se navega al enlace (`window.location.href`).
- Prevención de doble envío: se bloquea re-envío deshabilitando el botón o asignando `form.dataset.enviando = '1'` de forma síncrona sin bloquear la respuesta con timeouts artificiales.

### 4. Desambiguación Léxica en Vistas (`listar.blade.php`)
- Aplicar la Ley de Hick en los textos de botones del modal:
  - Confirmar plan: Título "Confirmar plan", botón OK "Sí, confirmar", botón Cancelar "Mantener pendiente".
  - Cancelar plan: Título "Cancelar plan", botón OK "Sí, cancelar", botón Cancelar "Mantener plan".
  - Eliminar plan: Título "Eliminar plan", botón OK "Sí, eliminar", botón Cancelar "Volver", estilo destructivo `.boton-rojo`.

## Verificación de Impacto
- La aplicación carga sin advertencias ni peticiones de red externas en la consola del navegador.
- Envíos de formularios y transiciones responden de inmediato sin el delay de 1000ms.
- El comportamiento en teclado (tecla Escape, foco) es completamente accesible.
- La suite de pruebas de PHPUnit se mantiene en verde.

