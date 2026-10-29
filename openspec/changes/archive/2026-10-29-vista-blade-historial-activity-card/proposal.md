# Proposal: Vista Blade de historial con tarjetas ActivityCard y filtros accesibles

## Issue Reference
- Sub-issue: Refs #16 (Closes #16)
- Requisito Docente / Madre: Refs #1

## Why
Tras implementar la persistencia (`PlanActivity`, sub-issue #14) y el controlador con endpoints temáticos (`HistorialController`, sub-issue #15), el sistema Planazo requería la capa visual y ergonómica de interfaz de usuario.

La sub-issue #16 diseña e implementa la vista `resources/views/planes/historial.blade.php`, el componente reutilizable `ActivityCard` con las 4 dimensiones explícitas de interacción, la barra de navegación modular con enlace accesible (`layouts/_partials/nav.blade.php`), el selector temático de $n=4$ chips conforme a la Ley de Hick y el estado vacío ilustrado.

## What Changes
1. **Componente de Navegación (`resources/views/layouts/_partials/nav.blade.php`):**
   - Modularización de la barra de navegación superior extraída de `layouts/app.blade.php`.
   - Inclusión de acceso directo a "Historial" con tamaño táctil ergonómico ($\ge 44\times 44$px, Ley de Fitts), estados activos y etiqueta `aria-label`.
2. **Componente ActivityCard (`resources/views/components/activity-card.blade.php`):**
   - Tarjeta estructurada con las 4 dimensiones de interacción:
     - Dimensión 1 (Qué ocurrió): Badge de acción con tokens semánticos y descripción legible.
     - Dimensión 2 (Cuándo): Marca temporal relativa (`diffForHumans()`) y absoluta en elemento `<time>`.
     - Dimensión 3 (Resultado): Badge de estado resultante (`confirmado`, `pendiente`, `cancelado`).
     - Dimensión 4 (Elemento involucrado): Plan asociado y usuario actor.
3. **Vista de Historial (`resources/views/planes/historial.blade.php`):**
   - Encabezado con navegación de regreso accesible.
   - Selector temático de $n=4$ chips (`Todos`, `Estados`, `Creaciones`, `Participación`) conforme a la Ley de Hick.
   - Estado vacío ilustrado con iconografía SVG temática y botón de acción ante colecciones vacías.
4. **Suite de Pruebas Feature (`tests/Feature/HistorialVistaTest.php`):**
   - 5 casos de prueba unitarios y de integración visual verificando renderizado de vista, presencia de enlace de navegación accesible, 4 dimensiones de `ActivityCard`, chips de Hick y estado vacío ilustrado.

## Capabilities

### New Capabilities
- `history-blade-ui`: Interfaz accesible para visualización estructurada de historial mediante ActivityCard y filtros temáticos ergonómicos.

## Impact
- `resources/views/layouts/_partials/nav.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/components/activity-card.blade.php`
- `resources/views/planes/historial.blade.php`
- `tests/Feature/HistorialVistaTest.php`
