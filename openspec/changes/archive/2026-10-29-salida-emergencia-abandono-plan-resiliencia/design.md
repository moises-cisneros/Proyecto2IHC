# Diseño: Salida de emergencia (H3), abandono de plan y suite de resiliencia

## 1. Política de Autorización (`app/Policies/PlanPolicy.php`)
- Nueva habilidad: `abandonar(User $user, Plan $plan): bool`
  - Si el usuario es el creador (`$user->id === $plan->user_id`): retorna `false` (el organizador no puede abandonar su propio plan; debe cancelarlo).
  - Si el usuario es un participante aceptado (`$this->isParticipante($user, $plan)`): retorna `true`.
  - En cualquier otro caso: retorna `false`.

## 2. Endpoint y Lógica de Abandono (`ColaboracionController@abandonar`)
- **Ruta:** `POST /planes/{plan}/abandonar` protegida por `auth`.
- **Autorización:** `Gate::authorize('abandonar', $plan)`.
- **Operaciones atómicas:**
  1. Identificar las invitaciones asociadas al usuario autenticado para ese plan (`$plan->invitaciones()->where(function ($q) use ($user) { $q->where('user_id', $user->id)->orWhere('email', $user->email); })->delete()`).
  2. Obtener los IDs de todas las opciones pertenecientes al plan: `$opcionIds = $plan->opciones()->pluck('id')`.
  3. Eliminar los votos emitidos por el usuario en esas opciones: `Voto::whereIn('opcion_id', $opcionIds)->where('user_id', $user->id)->delete()`.
  4. Redireccionar a `route('planes.listar')` con mensaje flash informativo: `¡Has abandonado el plan!`.

## 3. Interfaz y Diálogo de Confirmación (H3 & Fitts)
- Botón accesible "Abandonar plan" visible únicamente para participantes aceptados (no para el creador).
- Al pulsar, abre un `<dialog id="modal-abandonar">` que explica claramente las consecuencias:
  - "Al abandonar este plan, dejarás de figurar como colaborador y tus votos emitidos serán retirados."
- Botones de confirmación con `min-h-[44px]` y separación de 12px (`gap-3`) entre «Permanecer en el plan» y «Sí, abandonar plan».
