# Especificación: Confirmación de Plan, Resolución de Empates y Mejoras por Feedback

## Requisitos y Criterios del Sistema

### 1. Mejoras por Feedback de Usabilidad IHC (Sub-issue #46)

- **Confirmacisón Explícita de Fecha (H3, H5):** El selector de propuestas de fechas incluye un botón de confirmación explícita «Aceptar fecha». La fecha/hora seleccionada se retiene de forma reactiva y no se resetea al perder el foco o cambiar de pestaña.
- **Validación de Fechas Pasadas en Creación (H5, H9):**
  - Formulario de creación (`_modal_crear.blade.php`) contiene atributo `min` con la fecha actual.
  - Validación en backend (`StorePlanRequest`) con regla `after_or_equal:today` y mensaje descriptivo en español: *«La fecha del plan debe ser igual o posterior a la fecha actual.»*
- **Validación de Fechas Pasadas en Edición (H5, H9):**
  - Formulario de edición (`editar.blade.php`) incluye atributo `min` con la fecha actual.
  - Validación en backend (`UpdatePlanRequest`) con regla `after_or_equal:today`.

### 2. Persistencia y Dominio para Desempate y Cierre (Sub-issue #47)

- **Campos en Tabla `planes`:**
  - `propuesta_fecha_id` (foreignId nullable referenciando `opciones.id`, `nullOnDelete`).
  - `propuesta_lugar_id` (foreignId nullable referenciando `opciones.id`, `nullOnDelete`).
  - `motivo_desempate` (text nullable).
  - `fecha_confirmacion` (timestamp nullable).
- **Relaciones Eloquent:**
  - `propuestaFechaGanadora(): BelongsTo` a `Opcion`.
  - `propuestaLugarGanadora(): BelongsTo` a `Opcion`.
- **Métodos de Detección de Empates:**
  - `tieneEmpateFechas(): bool`: evalúa si 2 o más opciones de tipo `fecha` comparten el número máximo de votos (y votos > 0).
  - `tieneEmpateLugares(): bool`: evalúa si 2 o más opciones de tipo `lugar` comparten el número máximo de votos (y votos > 0).
  - `obtenerPropuestasEmpatadas(string $tipo)`: retorna colección de opciones que empatan en el primer puesto de votos.
  - `obtenerGanadorIndiscutido(string $tipo)`: retorna la opción líder estricta si no hay empate.
- **Inmutabilidad por Estado:** Un plan en estado `confirmado` o `cancelado` rechaza alteraciones directas en su estado deliberativo.

### 3. Controlador, Autorización y Lógica de Negocio (Sub-issue #50)

- **Endpoint Transaccional:** `POST /planes/{plan}/confirmar` orquestado por `PlanesController::confirmar` o controlador dedicado.
- **Form Request `ConfirmarPlanRequest`:**
  - Valida que `propuesta_fecha_id` pertenezca al plan y sea de tipo `fecha`.
  - Valida que `propuesta_lugar_id` (si se envía) pertenezca al plan y sea de tipo `lugar`.
  - Exige `motivo_desempate` obligatorio con mínimo 5 caracteres si existe empate en fechas o lugares más votados.
- **Autorización `PlanPolicy::confirmar`:** Solo el usuario creador/organizador (`$user->id === $plan->user_id`) puede confirmar el plan. Participantes o terceros reciben `403 Forbidden`.
- **Transición y Trazabilidad:** El plan pasa a `estado = 'confirmado'`, se registra la fecha de confirmación y un evento de auditoría en `PlanActivity`.

### 4. Interfaz y Experiencia Visual en Blade (Sub-issue #48)

- **Modal de Confirmación y Desempate (`<dialog>`):**
  - Accesible con backdrop, cierre por Escape y foco controlado.
  - Renderiza ganador directo o selector aislado de opciones empatadas (Ley de Hick: sólo opciones en empate).
  - Campo `<textarea name="motivo_desempate">` para ingresar la justificación obligatoria.
- **Banner de Veredicto Público (H1):** En planes confirmados, muestra la opción ganadora y el motivo del organizador visible para todos los miembros.
- **Congelamiento de Votos:** Botones de votar deshabilitados (`disabled`) y botón de sugerencias oculto bajo `@can`.
- **Acceso a Sala en Listado (Ley de Fitts):** Control de acceso en `listar.blade.php` con área táctil $\ge 44\times 44\text{px}$ sin desbordar la tabla ni romper los 800px de ancho.
- **Avatar Compacto en Cabecera:** Avatar del organizador redimensionado a tamaño armónico ($\le 48\times 48\text{px}$) en `_cabecera.blade.php`.
- **Actualización de `resources/DESIGN.md`:** Documentación de tokens y componentes antes de su uso en Blade.

### 5. Verificación Integral y Evidencias (Sub-issue #49)

- **Suite Playwright 1 (`feedback-1-selector-fechas.spec.ts`):** Verificación del selector de propuestas con botón «Aceptar fecha».
- **Suite Playwright 2 (`feedback-2-fechas-pasadas.spec.ts`):** Verificación de bloqueo de fechas pasadas en creación y edición.
- **Suite Playwright 3 (`tarea-7-confirmacion-desempate.spec.ts`):** Flujo completo de votación en empate, desempate con motivo, confirmación y vista participante inmutable.
- **Memoria de Entrega:** `evidencias/tarea-7.md` con enlaces a feedbacks, hashes de commits (`fix` y `feat`) y capturas de pantalla de evidencias.
