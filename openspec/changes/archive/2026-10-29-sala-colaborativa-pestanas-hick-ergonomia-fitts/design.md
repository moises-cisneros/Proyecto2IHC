# Diseño: Sala colaborativa, pestañas Hick y ergonomía Fitts

## 1. Arquitectura de Vistas (Diseño Atómico)

### `resources/views/planes/detalle.blade.php`
- Layout principal: `@extends('layouts.app')`.
- Estructura en tres bloques modulares que invocan parciales atómicos:
  1. `@include('planes._cabecera')`
  2. `@include('planes._participantes')`
  3. `@include('planes._sala_votacion')`

### Parcial `_cabecera.blade.php`
- **Elementos requeridos:**
  - Título del plan (`$plan->nombre`).
  - Badge accesible con el estado actual (`$plan->estado`).
  - Fecha límite de votación / decisión formateada (`$plan->fecha_limite`).
  - Nombre del organizador (`$plan->organizador`).
  - Botón de regreso accesible hacia «Mis planes» (`min-h-[44px]`).

### Parcial `_participantes.blade.php`
- **Elementos requeridos:**
  - Lista de colaboradores: organizador/creador con badge "Organizador" y participantes con invitación aceptada con badge "Participante".
  - Lista de invitaciones pendientes (visible para el organizador).
  - Botón accesible "Invitar colaborador" (`min-h-[44px]`) con diálogo nativo `<dialog id="modal-invitar">` para ingresar el correo electrónico.

### Parcial `_sala_votacion.blade.php`
- **Ergonomía de Hick ($T = b \log_2(n+1)$):**
  - Dos pestañas semánticas: «Fechas propuestas» y «Lugares propuestos» (reduciendo $n$ de opciones dispersas a dos dominios de decisión bien diferenciados).
  - Selector por pestaña con estado activo/inactivo accesible (`role="tablist"`, `role="tab"`, `aria-selected`).
- **Tarjetas de Opciones:**
  - Valor de la opción, usuario proponente y contador dinámico de votos.
  - Indicador visual de si el usuario autenticado ya emitió su voto.
  - Botón accesible de votar / retirar voto (`min-h-[44px]`).
- **Formulario / Modal para Proponer Opciones:**
  - Modal accesible `<dialog id="modal-proponer-opcion">` para agregar fecha o lugar.

## 2. Ergonomía de Fitts ($T = a + b \log_2(2D/W)$)
- Todo control interactivo posee `min-h-[44px]` y padding suficiente para garantizar $W \ge 44\text{px}$.
- Separación antagónica de al menos 12px (`gap-3` o `gap: 12px`) entre botones de acción y controles de cierre/cancelación.
