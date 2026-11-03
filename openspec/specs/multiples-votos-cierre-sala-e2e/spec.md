# Especificación: Múltiples Votos, Cierre de Sala Colaborativa y Pruebas E2E de Flujo Humano

## Requisitos y Criterios del Sistema

### 1. Dominio y Autorización (Sub-issue #34)
- **Voto Múltiple Independiente:** Un usuario puede emitir votos sobre múltiples opciones de fecha o lugar en un plan en estado `pendiente` cuya fecha límite no haya expirado.
- **Alternancia (Toggle):** Emitir un voto sobre una opción ya votada revoca el voto y decrementa el conteo de manera atómica.
- **Congelamiento de Sala Cerrada:**
  - `proponer` y `votar` están denegados con `403 Forbidden` si `$plan->estado !== 'pendiente'` o si `$plan->fecha_limite < now()->startOfDay()`.
  - `invitar` está permitido si `$plan->estado !== 'cancelado'` (creador puede invitar en `pendiente` y `confirmado`, bloqueado en `cancelado`).
- **Trazabilidad:** Cada emisión, retiro de voto y propuesta registra un evento en `PlanActivity`.

### 2. Interfaz y Experiencia de Usuario (Sub-issue #35)
- **Input Estructurado (H5):** El modal de propuestas utiliza `<input type="datetime-local">` para sugerencias de fechas y `<input type="text">` para lugares.
- **Banner de Cierre:** En planes confirmados se renderiza un banner informativo visible (`role="status"`) que comunica que la deliberación está finalizada.
- **Inhabilitación de Controles:** Los botones de votación en planes confirmados están deshabilitados (`disabled`) con mensaje de estado cerrado. El botón de proponer se oculta mediante directiva `@can('proponer', $plan)`.
- **Botonera Horizontal Ergonómica (Fitts):** En `listar.blade.php`, las acciones se organizan horizontalmente con `min-height: 44px` y separación de `gap: 8px`, evaluando permisos con directivas `@can`.

### 3. Automatización E2E en Playwright (Sub-issue #36)
- Tarea humana «Anita»: Login, navegación a sala abierta, emisión de múltiples votos simultáneos, decremento por toggle, acceso a sala cerrada y comprobación de inmutabilidad.
- Modo visible interactivo (`--headed` / `--ui`).
- Verificación de accesibilidad con `@axe-core/playwright` sin violaciones críticas.

### 4. Matriz de Evidencias (Sub-issue #37)
- Archivo `evidencias/tarea-6.md` con matriz comparativa antes/después, capturas de pantalla de la interacción y justificación de directivas de usabilidad.
