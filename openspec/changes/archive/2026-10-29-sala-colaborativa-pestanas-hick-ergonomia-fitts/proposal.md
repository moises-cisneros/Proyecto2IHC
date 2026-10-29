# Propuesta: Sala colaborativa, pestañas Hick y ergonomía Fitts

## Referencia
- **Sub-issue:** #9 — `[Interfaz] — Sala colaborativa, pestañas Hick y ergonomía Fitts (Refs #6)`
- **Issue Padre:** #6 — `[Requisito Docente] — Tarea 5: Funcionalidad colaborativa de votos y sugerencias`

## Contexto
Planazo requiere un espacio interactivo centralizado donde el organizador y los participantes puedan colaborar en la toma de decisiones para un plan grupal. Dicha sala debe permitir visualizar el estado actual del plan, la lista de colaboradores con sus roles, y una sala de votación estructurada para sugerir y votar fechas y lugares. Siguiendo las directrices ergonómicas de IHC, la interfaz debe aplicar la Ley de Hick para reducir la carga de decisión agrupando opciones en pestañas semánticas (Fechas vs Lugares) y la Ley de Fitts garantizando áreas de toque de al menos 44×44px y separación antagónica preventiva (`gap-3`) entre acciones clave.

## Objetivos
1. Diseñar la vista principal de la sala colaborativa en `resources/views/planes/detalle.blade.php`.
2. Descomponer la vista en parciales atómicos modulares:
   - `resources/views/planes/_cabecera.blade.php`: Título, badge de estado, fecha límite y organizador.
   - `resources/views/planes/_participantes.blade.php`: Lista de colaboradores con sus roles (Organizador vs Participante) y botón accesible "Invitar".
   - `resources/views/planes/_sala_votacion.blade.php`: Pestañas semánticas (Ley de Hick: Fechas vs Lugares), tarjetas de opciones con contador de votos y botones de votar.
3. Habilitar la ruta `GET /planes/{plan}` en `routes/web.php` conectada a `ColaboracionController@detalle` con autorización de lectura (`view`).
4. Aplicar ergonomía de Fitts y Hick:
   - Botones con área de contacto táctil de al menos 44×44px (`min-h-[44px]`).
   - Separación antagónica de al menos 12px (`gap-3`) entre acciones de votar, proponer o volver.
5. Desarrollar la suite de pruebas `tests/Feature/SalaColaborativaVistaTest.php` con al menos 4 casos de prueba verificados en PHPUnit.
