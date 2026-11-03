# Proposal: Hardening de Autenticación, Integridad Referencial y Auditoría Completa

## Issue Reference

- Requisito Madre: Refs #29
- Sub-issues asociadas:
  - Sub-issue 1 (Persistencia / Backend): Refs #30 (Closes #30)
  - Sub-issue 2 (Controlador / Seguridad): Refs #31 (Closes #31)
  - Sub-issue 3 (Historial / Auditoría): Refs #32 (Closes #32)

## Why

El proyecto Planazo presentaba brechas de seguridad, deuda técnica en la máquina de estados y falta de trazabilidad en acciones colaborativas detectadas en la auditoría técnica:

1. **Integridad referencial y autoría mutable (#30):** La clave foránea `planes.user_id` no tenía restricción explícita de cascada en base de datos; el modelo `User` carecía del método `planes(): HasMany` definido en `ARCHITECTURE.md`; el formulario de edición permitía modificar manualmente el campo `organizador` violando la inmutabilidad de la identidad (Nielsen H5/H6); y la máquina de estados permitía reactivar planes cancelados hacia pendiente o confirmado.
2. **Seguridad en sesiones y autenticación (#31):** El método `AutentificacionController::salir` cerraba la sesión sin invocar `invalidate()` ni `regenerateToken()`, exponiendo a fijación de sesión; la recuperación de contraseña filtraba la existencia de cuentas respondiendo errores específicos; y las rutas públicas de autenticación carecían de aislamiento con middleware `guest` y rate limiting contra ataques de fuerza bruta.
3. **Trazabilidad incompleta en auditoría (#32):** Las acciones colaborativas clave (invitar colaboradores, proponer opciones de fecha/lugar, votar y desvincularse/abandonar plan) no persistían eventos en el historial `PlanActivity`, rompiendo la trazabilidad completa del ciclo de vida del plan y el aislamiento por usuario en `/historial`.

## What Changes

1. **Sub-issue #30 — Backend / Persistencia:**
   - Crear migración para establecer clave foránea `foreign('user_id')->references('id')->on('users')->cascadeOnDelete()` en `planes`.
   - Incorporar relación `planes(): HasMany` en `App\Models\User`.
   - En `PlanesController::actualizar` y `resources/views/planes/editar.blade.php`, remover el campo editable de `organizador`, manteniendo su valor inmutable e ilustrado mediante un badge de solo lectura acorde a `resources/DESIGN.md`.
   - En `PlanesController::cambiarEstado`, impedir cualquier reactivación de planes con estado `cancelado` (hacia `pendiente` o `confirmado`).
   - Crear suite de pruebas `tests/Feature/IntegridadPlanesTest.php` con al menos 4 casos de prueba (incluyendo seguridad y rechazo 403).

2. **Sub-issue #31 — Controlador / Seguridad:**
   - En `AutentificacionController::salir`, destruir la sesión invocando `$request->session()->invalidate()` y regenerar el token CSRF con `$request->session()->regenerateToken()`.
   - En `AutentificacionController::enviarCorreo`, responder con mensaje genérico y uniforme tanto si el correo existe como si no («Si el correo ingresado está registrado, te enviamos un enlace de recuperación.») previniendo ataques de enumeración.
   - En `routes/web.php`, envolver las rutas `/ingresar`, `/registro`, `/recuperar` y `/restablecer` bajo el middleware `guest` y aplicar rate limiting (`throttle:5,1` / `throttle:login`).
   - Crear suite de pruebas `tests/Feature/HardeningAutenticacionTest.php` con al menos 4 casos de prueba.

3. **Sub-issue #32 — Historial / Auditoría:**
   - En `ColaboracionController::invitar`, registrar evento `invitacion` en `PlanActivity` para el plan y usuario.
   - En `ColaboracionController::guardarOpcion`, registrar evento `propuesta` en `PlanActivity`.
   - En `ColaboracionController::votar`, registrar evento `voto` en `PlanActivity`.
   - En `ColaboracionController::abandonar`, registrar evento de desvinculación en `PlanActivity` antes de eliminar las membresías.
   - Garantizar aislamiento de `/historial` donde cada usuario solo pueda visualizar sus propias actividades y filtrar por las 4 categorías ergonómicas de Hick.
   - Crear suite de pruebas `tests/Feature/TrazabilidadColaboracionTest.php` con al menos 4 casos de prueba.

## Capabilities

### Modified Capabilities

- `plan-lifecycle-integrity`: Garantiza integridad referencial en BD, inmutabilidad de autoría y bloqueo de transición sobre planes cancelados.
- `session-authentication-hardening`: Blindaje de logout con invalidación completa de sesión, protección contra enumeración de emails y middleware `guest` con rate limiting.
- `collaborative-activity-audit`: Trazabilidad obligatoria y persistente de todas las acciones colaborativas dentro de `PlanActivity`.

## Impact

- `database/migrations/`: Nueva migración de clave foránea.
- `app/Models/User.php`: Método `planes()`.
- `app/Http/Controllers/PlanesController.php`: Inmutabilidad de organizador y blindaje de cancelados.
- `resources/views/planes/editar.blade.php`: Badge de solo lectura para organizador.
- `app/Http/Controllers/AutentificacionController.php`: Logout seguro y anti-enumeración.
- `routes/web.php`: Middleware `guest` y rate limiting.
- `app/Http/Controllers/ColaboracionController.php`: Emisión de eventos en `PlanActivity`.
- `tests/Feature/`: Nuevas suites unitarias y de integración para cada sub-issue.
