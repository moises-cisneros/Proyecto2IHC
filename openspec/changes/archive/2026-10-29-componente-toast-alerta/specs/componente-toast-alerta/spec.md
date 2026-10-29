# componente-toast-alerta Specification

## Purpose
Proveer un mecanismo de notificación flotante (Toast) accesible, auto-ocultable y desacoplado del flujo del documento HTML para mensajes flash y eventos de interfaz en Planazo.

## Requirements

### Requirement: Posicionamiento flotante y ausencia de desplazamiento de diseño (Zero CLS)
El componente de notificación flash debe ubicarse fuera del flujo normal del documento mediante posicionamiento fijo para no provocar saltos visuales verticales.

#### Scenario: Renderizado de notificación flash en pantalla
- **Given** una sesión con un mensaje flash (`exito` o `error`)
- **When** se carga cualquier vista que incluya el layout maestro o de acceso
- **Then** el contenedor de notificación debe renderizarse flotante en la esquina superior derecha (`fixed top-5 right-5 z-50`)
- **And** no debe modificar las coordenadas ni empujar hacia abajo los elementos adyacentes del contenedor principal

### Requirement: Ocultamiento automático tras 3 segundos con transición suave
La notificación debe permanecer legible durante 3000ms antes de desvanecerse de la vista de forma automática.

#### Scenario: Ciclo de vida temporal del Toast
- **Given** una notificación Toast visible en pantalla
- **When** transcurren 3000ms desde su renderizado
- **Then** el Toast debe iniciar una transición de salida suave (`opacity: 0`, traslación o desvanecimiento)
- **And** una vez culminada la animación, el elemento debe removerse del árbol DOM

### Requirement: Botón de descarte manual accesible (Nielsen H3 & Fitts)
El usuario debe poder cerrar la notificación en cualquier instante previo al temporizador mediante un control interactivo accesible.

#### Scenario: Cierre manual por interacción del usuario
- **Given** una notificación Toast activa
- **When** el usuario presiona el botón de cierre manual `×`
- **Then** el temporizador automático debe cancelarse inmediatamente
- **And** la notificación debe ocultarse y eliminarse del DOM sin esperar a los 3 segundos
- **And** el botón de cierre debe disponer de un área táctil mínima de 44×44px y un atributo `aria-label="Cerrar notificación"`

### Requirement: Función auxiliar global en JavaScript
El cliente debe poder disparar notificaciones Toast de forma dinámica e imperativa mediante una función global en el objeto `window`.

#### Scenario: Invocación programática desde ui.js
- **Given** el script cliente `public/js/ui.js` cargado en la página
- **When** se invoca `window.mostrarToast(mensaje, tipo)` con un mensaje y tipo (`exito` o `error`)
- **Then** se debe crear o reutilizar el contenedor flotante e insertar un elemento Toast con los mismos estilos, temporizador de 3s y botón de cierre

### Requirement: Reemplazo integral en layouts y vistas
Las antiguas alertas estáticas deben ser sustituidas por el componente Toast en todos los puntos de entrada de alertas de sesión.

#### Scenario: Migración de layouts/_partials/mensajes.blade.php
- **Given** el archivo parcial `resources/views/layouts/_partials/mensajes.blade.php`
- **When** se procesa la directiva `@include('layouts._partials.mensajes')`
- **Then** debe instanciar `<x-toast-alerta />` en lugar de bloques estáticos `.alerta`

