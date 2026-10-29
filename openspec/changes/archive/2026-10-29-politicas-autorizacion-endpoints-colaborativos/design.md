# Diseño: Políticas de autorización (PlanPolicy) y endpoints colaborativos

## 1. Arquitectura de Autorización

### Capa de Políticas (`app/Policies/PlanPolicy.php`)
- **Organizador / Creador (`$user->id === $plan->user_id`):**
  - Puede actualizar (`update`), eliminar (`delete`), cambiar estado (`cambiarEstado`) e invitar usuarios (`invitar`).
  - Puede proponer opciones (`proponer`) y votar (`votar`).
- **Participante Aceptado (`$plan->participantes()->where('users.id', $user->id)->exists()`):**
  - Puede ver el plan (`view`), proponer opciones de fecha/lugar (`proponer`) y votar en opciones del plan (`votar`).
  - **No puede** invitar, eliminar ni cambiar el estado del plan (403 Forbidden).
- **Invitado Pendiente / No Invitado:**
  - Solo puede ver invitaciones destinadas a su correo si aplica; no puede proponer opciones, votar, editar, cancelar ni eliminar (403 Forbidden).

## 2. Endpoints Colaborativos (`app/Http/Controllers/ColaboracionController.php`)

### `POST /planes/{plan}/invitar`
- **Autorización:** `Gate::authorize('invitar', $plan)`.
- **Validación:**
  - `email`: `required|email|max:255`.
- **Comportamiento:**
  - Genera `token` aleatorio seguro (`Str::random(40)`).
  - Asocia `user_id` si el usuario con ese email ya existe en la base de datos; de lo contrario `null`.
  - Crea o actualiza registro en `invitaciones` con `estado = 'pendiente'`.
  - Registra actividad en `PlanActivity` si aplica.
  - Redirecciona con mensaje flash de confirmación.

### `POST /planes/{plan}/opciones`
- **Autorización:** `Gate::authorize('proponer', $plan)`.
- **Validación:**
  - `tipo`: `required|in:fecha,lugar`.
  - `valor`: `required|string|max:255`.
  - `descripcion`: `nullable|string|max:500`.
- **Comportamiento:**
  - Crea registro en `opciones` asociado al `plan_id` y al `auth()->id()`.
  - Redirecciona con mensaje flash.

### `POST /opciones/{opcion}/votar`
- **Autorización:** `Gate::authorize('votar', $opcion->plan)`.
- **Comportamiento:**
  - Si el usuario ya votó por esta opción, se remueve el voto (toggle) o se mantiene según convención ergonómica.
  - Si no ha votado, se crea el registro en `votos` (`opcion_id`, `user_id`).
  - Redirecciona con mensaje flash.

## 3. Protección de Endpoints Existentes (`PlanesController`)
- `eliminar(Plan $plan)`: `Gate::authorize('delete', $plan)`.
- `cambiarEstado(Request $request, Plan $plan)`: `Gate::authorize('cambiarEstado', $plan)`.
- `actualizar(Request $request, Plan $plan)`: `Gate::authorize('update', $plan)`.
- `editar(Plan $plan)`: `Gate::authorize('update', $plan)`.
