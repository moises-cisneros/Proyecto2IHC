# Especificación: Sala colaborativa, pestañas Hick y ergonomía Fitts

## Requisitos

### Criterios de Aceptación
1. **Vista Principal (`resources/views/planes/detalle.blade.php`):**
   - Renderiza la sala colaborativa descompuesta en parciales atómicos.
   - Accesible para el organizador y participantes aceptados mediante `GET /planes/{plan}`.
2. **Parcial Cabecera (`_cabecera.blade.php`):**
   - Muestra el nombre del plan, badge de estado, fecha límite y organizador.
3. **Parcial Participantes (`_participantes.blade.php`):**
   - Lista a los colaboradores diferenciando roles (Organizador vs Participante).
   - Incluye botón accesible "Invitar" con área mínima de contacto táctil de 44×44px.
4. **Parcial Sala de Votación (`_sala_votacion.blade.php`):**
   - Divide la votación en pestañas semánticas aplicando la Ley de Hick (Fechas vs Lugares).
   - Presenta tarjetas de opciones individuales con descripción, proponente y contador dinámico de votos.
   - Botón para votar o retirar voto con feedback claro.
5. **Ergonomía de Fitts:**
   - Botones con `min-h-[44px]` y separación antagónica mínima de 12px (`gap-3`) entre votar y cancelar/cerrar.
