# Design: Hardening de Autenticación, Integridad Referencial y Auditoría Completa

## Contexto y Arquitectura

Este diseño implementa de forma desacoplada y progresiva los tres sub-issues que componen el issue de hardening #29:

- **Sub-issue #30:** Capa de persistencia, integridad de modelo y reglas de inmutabilidad en Planes.
- **Sub-issue #31:** Capa de control de acceso, seguridad de sesiones, prevención de enumeración y límites de tasa en rutas públicas.
- **Sub-issue #32:** Capa de trazabilidad de eventos y auditoría en Colaboración e Historial.

## Arquitectura Detallada por Componente

### 1. Persistencia y Máquina de Estados (Sub-issue #30)

- **Migración de clave foránea:**
  - Archivo: `database/migrations/2026_11_03_000001_agregar_foreign_key_user_id_a_planes_table.php`
  - Define `foreignId('user_id')->references('id')->on('users')->cascadeOnDelete()` protegiendo la integridad en cascada si un usuario es dado de baja.
- **Relación en modelo User:**
  - `User::planes(): HasMany` retornando planes creados por el usuario, complementando `planesParticipados(): BelongsToMany`.
- **Inmutabilidad del organizador:**
  - En `PlanesController::actualizar`, eliminar `organizador` de la validación y de la asignación del modelo. El valor de `$plan->organizador` permanece inmutable.
  - En `resources/views/planes/editar.blade.php`, reemplazar el `<input name="organizador">` por un badge informativo de solo lectura según las directrices de `resources/DESIGN.md` (`#f4f4f0`, borde `#e2e2dc`, tipografía de apoyo atenuada).
- **Restricción de reactivación:**
  - En `PlanesController::cambiarEstado`, verificar si `$plan->estado === 'cancelado' && $request->estado !== 'cancelado'`, retornando error flash «Un plan cancelado no puede reactivarse.».

### 2. Seguridad en Autenticación y Rutas (Sub-issue #31)

- **Invalidación de sesión en Logout:**
  - En `AutentificacionController::salir(Request $request)`:

    ```php
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
    ```

- **Anti-enumeración de usuarios:**
  - En `AutentificacionController::enviarCorreo`:
    - Independientemente del retorno de `Password::sendResetLink`, capturar cualquier excepción o estado fallido y responder de forma idéntica:
    - `return back()->with('exito', 'Si el correo ingresado está registrado, te enviamos un enlace de recuperación.');`
- **Protección de Rutas (Guest y Rate Limiting):**
  - En `routes/web.php`, aislar rutas públicas de acceso bajo `Route::middleware('guest')`.
  - Aplicar middleware de limitación `throttle:5,1` o `throttle:login` en `POST /ingresar` y `POST /recuperar`.

### 3. Trazabilidad en Acciones Colaborativas (Sub-issue #32)

- **Registro en `PlanActivity`:**
  - En `ColaboracionController::invitar`:

    ```php
    $plan->actividades()->create([
        'user_id' => auth()->id(),
        'accion' => 'invitacion',
        'descripcion' => 'Invitación enviada a ' . $request->email,
        'estado_resultante' => $plan->estado,
    ]);
    ```

  - En `ColaboracionController::guardarOpcion`:

    ```php
    $plan->actividades()->create([
        'user_id' => auth()->id(),
        'accion' => 'propuesta',
        'descripcion' => auth()->user()->name . ' propuso una nueva opción de ' . $request->tipo . ': ' . $request->valor,
        'estado_resultante' => $plan->estado,
    ]);
    ```

  - En `ColaboracionController::votar`:

    ```php
    $plan->actividades()->create([
        'user_id' => auth()->id(),
        'accion' => 'voto',
        'descripcion' => auth()->user()->name . ' ' . ($votoExistente ? 'retiró su voto de' : 'votó por') . ' la opción: ' . $opcion->valor,
        'estado_resultante' => $plan->estado,
    ]);
    ```

  - En `ColaboracionController::abandonar`:

    ```php
    $plan->actividades()->create([
        'user_id' => $usuario->id,
        'accion' => 'participacion',
        'descripcion' => $usuario->name . ' abandonó el plan.',
        'estado_resultante' => $plan->estado,
    ]);
    ```

- **Aislamiento en `/historial`:**
  - `HistorialController::index` mantiene filtro estricto por `auth()->id()`.

## Consideraciones de IHC y Usabilidad

- **Nielsen H5 (Prevención de errores):** El usuario no puede alterar accidentalmente la autoría ni reactivar un plan archivado/cancelado.
- **Nielsen H6 (Reconocimiento antes que recuerdo):** La interfaz visualiza al organizador en badge sin requerir entrada motriz ni memoria.
- **Ley de Fitts & Hick:** Mantener botones y tabs en la sala colaborativa y filtros de historial en $n=4$ chips predecibles.
