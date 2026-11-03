# Diseño: Múltiples Votos, Cierre de Sala Colaborativa y Pruebas E2E de Flujo Humano

## 1. Arquitectura de Autorización y Dominio (`app/Policies/PlanPolicy.php`)

### Reglas de Estado del Plan y Deliberación
- **Plan Abierto (`pendiente` y no vencido):**
  - Creador y participantes pueden `votar` en cualquier opción del plan de forma independiente.
  - Creador y participantes pueden `proponer` opciones de fecha o lugar.
  - Creador puede `invitar` colaboradores.
- **Plan Confirmado (`confirmado`):**
  - La deliberación queda **congelada**: `votar` y `proponer` retornan `false`. Cualquier petición HTTP a `POST /opciones/{opcion}/votar` o `POST /planes/{plan}/opciones` es rechazada con `403 Forbidden`.
  - El plan sigue aceptando nuevos miembros: `invitar` retorna `true` para el creador (permite sumar personas al evento acordado).
- **Plan Cancelado (`cancelado`):**
  - Todas las operaciones quedan estrictamente bloqueadas: `votar`, `proponer`, `invitar`, `update`, `aceptar` retornan `false` (rechazo `403 Forbidden`).
- **Plan Vencido (`fecha_limite < now()->startOfDay()`):**
  - Se considera deliberación expirada: `votar` y `proponer` retornan `false`.

### Implementación en `PlanPolicy.php`:
```php
public function proponer(User $user, Plan $plan): bool
{
    if ($plan->estado !== 'pendiente' || ($plan->fecha_limite && $plan->fecha_limite < now()->startOfDay())) {
        return false;
    }

    return $this->isCreatorOrParticipante($user, $plan);
}

public function votar(User $user, Plan $plan): bool
{
    if ($plan->estado !== 'pendiente' || ($plan->fecha_limite && $plan->fecha_limite < now()->startOfDay())) {
        return false;
    }

    return $this->isCreatorOrParticipante($user, $plan);
}

public function invitar(User $user, Plan $plan): bool
{
    if ($plan->estado === 'cancelado') {
        return false;
    }

    return $this->isCreator($user, $plan);
}
```

---

## 2. Controlador y Auditoría (`ColaboracionController.php`)
- `votar(Request $request, Opcion $opcion)`:
  - Invoca `Gate::authorize('votar', $opcion->plan)`.
  - Si el usuario ya votó por la opción, elimina el voto (toggle) y decrementa el conteo.
  - Si no ha votado, crea el registro en `votos` (`opcion_id`, `user_id`).
  - Registra actividad en `PlanActivity` con acción `'voto'`.
- `guardarOpcion(Request $request, Plan $plan)`:
  - Invoca `Gate::authorize('proponer', $plan)`.
  - Valida y crea la opción.
  - Registra actividad en `PlanActivity` con acción `'propuesta'`.

---

## 3. Interfaz y Ergonomía (IHC)

### Modal de Propuestas Estructurado (Nielsen H5)
- En `resources/views/planes/_sala_votacion.blade.php`:
  - Selector de tipo: `<select id="select-tipo-opcion">` ("fecha" vs "lugar").
  - Si se elige "fecha", se muestra `<input type="datetime-local">` con `required`.
  - Si se elige "lugar", se muestra `<input type="text">` con placeholder descriptivo.
  - El script del modal actualiza la visibilidad de los campos en base a la pestaña activa o al cambio de selección.

### Sala Colaborativa Cerrada / Confirmada
- Banner accesible destacado:
  - `role="status"`
  - Fondo de éxito suave (`#ecfdf5`), borde `#a7f3d0`, texto verde oscuro `#065f46`.
  - Icono y mensaje: «Votación finalizada. Este plan ha sido confirmado y la deliberación se encuentra cerrada».
- Bloqueo visual de controles:
  - Ocultar botón `+ Proponer opción` con `@can('proponer', $plan)`.
  - En las tarjetas de opciones: si `@cannot('votar', $plan)`, renderizar botón deshabilitado `<button disabled>` con texto «Votación cerrada» o badge de votos final.

### Corrección Ergonómica de la Botonera en Listado (`listar.blade.php`)
- Sustituir la columna de botones verticales de 30px (`.botones-columna`) por una botonera horizontal accesible:
  - Contenedor con `display: flex; gap: 8px; align-items: center;`.
  - Botones con `min-height: 44px`, `min-width: 44px`, padding generoso y sin texto deformado.
  - Directivas `@can('cambiarEstado', $plan)`, `@can('update', $plan)` y `@can('delete', $plan)`.

---

## 4. Pruebas E2E en Playwright (`e2e/tarea-6-varios-votos.spec.ts`)
- Narrativa de usuario: «Anita» (usando credenciales de prueba `test@example.com` o seeders precargados).
- Flujo 1: Anita inicia sesión, entra a la sala abierta, vota por 2 opciones distintas (una fecha y un lugar), comprueba que ambos contadores aumentan, retira su voto de la fecha y ve decrementarse el contador de inmediato.
- Flujo 2: Anita accede a un plan confirmado y verifica que los botones de voto están inhabilitados y la votación cerrada.
- Auditoría Axe: Escaneo de accesibilidad en la sala con `@axe-core/playwright`.

---

## 5. Matriz de Evidencias (`evidencias/tarea-6.md`)
- Documento markdown en la raíz `evidencias/tarea-6.md` estructurando:
  1. Tabla comparativa "Antes vs Después" de cada criterio funcional y no funcional.
  2. Evidencias visuales de pruebas antes de la feature (`docs/evidencias/tarea-6/antes/`).
  3. Evidencias visuales de pruebas después de la feature (`docs/evidencias/tarea-6/despues/`).
  4. Análisis heurístico y ergonómico (Fitts, Hick, Nielsen H5).
