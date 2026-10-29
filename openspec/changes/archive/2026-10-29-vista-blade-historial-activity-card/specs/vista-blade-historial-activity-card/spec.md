# vista-blade-historial-activity-card Specification

## Purpose
Proveer la interfaz de usuario ergonómica, accesible y estructurada para la consulta y navegación del historial de actividades en Planazo.

## Requirements

### Requirement: Enlace accesible a Historial en barra de navegación (Ley de Fitts)
La barra de navegación principal debe ofrecer un acceso directo al módulo de historial con un área táctil mínima de 44×44px y etiqueta accesible.

#### Scenario: Visualización y área de pulsación de enlace Historial
- **Given** un usuario autenticado navegando en cualquier vista que use el layout maestro
- **When** se evalúa el elemento de enlace al Historial en `layouts/_partials/nav.blade.php`
- **Then** el enlace apunta a la ruta `historial.index`
- **And** posee dimensiones de pulsación mínimas de 44×44px conforme a la Ley de Fitts
- **And** dispone de un atributo descriptivo `aria-label="Ir al Historial de Actividad"`

### Requirement: Estructuración en 4 dimensiones de ActivityCard
Cada tarjeta de evento en el historial debe explicitar de forma clara las 4 dimensiones de interacción: qué ocurrió, cuándo ocurrió, cuál fue el resultado y qué elemento involucró.

#### Scenario: Presentación multidimensional de un evento
- **Given** un evento de actividad registrado en la colección
- **When** se renderiza mediante el componente `<x-activity-card :actividad="$actividad" />`
- **Then** muestra el tipo de acción y resumen (Qué ocurrió)
- **And** muestra el tiempo relativo y absoluto (Cuándo ocurrió)
- **And** muestra el badge con el estado resultante (Resultado)
- **And** muestra el plan involucrado y usuario autor (Elemento involucrado)

### Requirement: Selector temático de chips según la Ley de Hick (n = 4)
La vista de historial debe presentar exactamente 4 chips de filtrado temático (`Todos`, `Estados`, `Creaciones`, `Participación`) para minimizar la sobrecarga de toma de decisiones.

#### Scenario: Interacción con chips temáticos
- **Given** la vista de historial cargada
- **When** el usuario inspecciona el grupo de filtros
- **Then** se encuentran presentes exactamente 4 opciones temáticas
- **And** el chip activo refleja el estado con `aria-pressed="true"`

### Requirement: Estado vacío ilustrado
Cuando la consulta no arroje resultados o el usuario carezca de eventos en la categoría seleccionada, la interfaz debe mostrar un estado vacío amigable con llamado a la acción.

#### Scenario: Visualización de estado vacío
- **Given** una consulta de historial sin actividades
- **When** se procesa la vista `planes.historial`
- **Then** se despliega una ilustración SVG temática, título explicativo y botón para regresar o gestionar planes
