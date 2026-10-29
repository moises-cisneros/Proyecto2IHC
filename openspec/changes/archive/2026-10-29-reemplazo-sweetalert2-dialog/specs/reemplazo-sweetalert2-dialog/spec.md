# reemplazo-sweetalert2-dialog Specification

## Purpose
Estandarizar las confirmaciones modales y suprimir dependencias externas de CDNs y retrasos artificiales en Planazo mediante el elemento nativo HTML5 `<dialog>`.

## Requirements

### Requirement: Cero dependencias externas de CDNs en layouts
El layout maestro de la aplicación debe operar de forma totalmente autónoma sin inyectar librerías externas de confirmación o barras de carga vía CDN.

#### Scenario: Carga limpia de layouts/app.blade.php
- **Given** el layout maestro `resources/views/layouts/app.blade.php`
- **When** se inspeccionan las cabeceras `<head>` y el pie de página
- **Then** no deben existir etiquetas `<link>` ni `<script>` que apunten a SweetAlert2 ni NProgress
- **And** la aplicación debe poder funcionar y renderizar correctamente sin conexión a internet

### Requirement: Componente modal accesible dialog-confirm
El sistema debe proveer un componente nativo Blade `resources/views/components/dialog-confirm.blade.php` basado en `<dialog>`.

#### Scenario: Presentación y ergonomía del diálogo modal
- **Given** el componente `resources/views/components/dialog-confirm.blade.php`
- **When** se incluye en la vista o se abre mediante la API nativa `.showModal()`
- **Then** debe renderizar un elemento `<dialog>` con backdrop nativo (`rgba(0, 0, 0, 0.4)`), fondo blanco y esquinas redondeadas de 12px
- **And** los botones de acción deben contar con un área táctil mínima de 44×44px y una separación preventiva de mínimo 12px (`gap-3`) conforme a la Ley de Fitts

### Requirement: Eliminación de retardos artificiales
La interfaz debe responder de forma inmediata a las interacciones del usuario sin demoras forzadas en JavaScript.

#### Scenario: Envío de formularios e interacciones en ui.js
- **Given** el script de interfaz `public/js/ui.js`
- **When** el usuario confirma una acción o envía un formulario
- **Then** no debe ejecutarse ninguna espera artificial forzada de 1000ms (`TIEMPO_MINIMO`)
- **And** el envío de datos o redirección debe ocurrir de forma inmediata

### Requirement: Flujos de confirmación de planes con dialog nativo
Las operaciones críticas de cambio de estado y eliminación de planes deben solicitar confirmación preventiva mediante el diálogo nativo con desambiguación léxica.

#### Scenario: Confirmación de cancelación o eliminación en mis planes
- **Given** la lista de planes en `resources/views/planes/listar.blade.php`
- **When** el usuario pulsa en el botón de Cancelar o Eliminar plan
- **Then** se despliega el `<dialog>` nativo detallando el nombre del plan y la consecuencia de la acción
- **And** las etiquetas de los botones evitan ambigüedad léxica siguiendo la Ley de Hick (ej. «Mantener plan» vs «Sí, cancelar»)

