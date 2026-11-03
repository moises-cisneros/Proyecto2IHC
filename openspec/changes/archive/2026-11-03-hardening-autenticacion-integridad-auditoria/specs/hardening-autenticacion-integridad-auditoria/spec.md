# Spec: Hardening de Autenticación, Integridad Referencial y Auditoría Completa

## ADDED REQUIREMENTS

### REQ-01: Clave Foránea e Integridad Referencial en Planes

- **Descripción:** La tabla `planes` debe tener una restricción de clave foránea en la columna `user_id` referenciando a `users.id` con eliminación en cascada (`cascadeOnDelete`).
- **Verificación:** Si se elimina un usuario, sus planes asociados deben ser removidos en cascada por la base de datos sin dejar registros huérfanos.

### REQ-02: Relación Eloquent User::planes()

- **Descripción:** El modelo `App\Models\User` debe exponer la relación `planes(): HasMany` vinculada a `App\Models\Plan`.
- **Verificación:** Invocar `$user->planes` debe retornar una colección con los planes creados por dicho usuario.

### REQ-03: Inmutabilidad del Organizador en Edición

- **Descripción:** En la vista de edición `resources/views/planes/editar.blade.php`, el nombre del organizador se muestra exclusivamente como badge informativo no editable. En `PlanesController::actualizar`, el campo `organizador` no es editable ni modificable mediante payload HTTP.
- **Verificación:** Peticiones `PUT /planes/{plan}` con un campo `organizador` alternativo deben ignorar dicho valor o mantener inalterado el organizador original.

### REQ-04: Máquina de Estados de Planes Cancelados

- **Descripción:** Un plan en estado `cancelado` es terminal y no puede ser transicionado de vuelta a `pendiente` ni a `confirmado`.
- **Verificación:** Intentar `PUT /planes/{plan}/estado` con `estado=pendiente` o `estado=confirmado` sobre un plan cancelado retorna error de validación o flash informativo y no muta el estado.

### REQ-05: Cierre de Sesión Seguro (Logout Hardening)

- **Descripción:** `AutentificacionController::salir` debe invalidar la sesión (`$request->session()->invalidate()`) y regenerar el token CSRF (`$request->session()->regenerateToken()`).
- **Verificación:** Tras el logout, el ID de sesión anterior queda revocado y el token CSRF es modificado.

### REQ-06: Neutralización de Enumeración de Usuarios

- **Descripción:** El endpoint `POST /recuperar` debe retornar un mensaje neutro («Si el correo ingresado está registrado, te enviamos un enlace de recuperación.») tanto si el correo existe como si no existe.
- **Verificación:** Peticiones con emails existentes o inexistentes reciben idéntica respuesta HTTP de éxito con el mensaje neutro.

### REQ-07: Aislamiento Guest y Límite de Tasa (Rate Limiting)

- **Descripción:** Las rutas `/ingresar`, `/registro`, `/recuperar` y `/restablecer` están protegidas por el middleware `guest`, redirigiendo usuarios autenticados a `/mis-planes`. Las rutas POST de autenticación aplican rate limiting.
- **Verificación:** Un usuario con sesión activa que visita `/ingresar` o `/registro` es redirigido (`302`) a `/mis-planes`. Excesivos intentos consecutivos de login son bloqueados.

### REQ-08: Trazabilidad Integral en Colaboración

- **Descripción:** Todas las acciones colaborativas (`invitar`, `guardarOpcion`, `votar`, `abandonar`) registran un registro inmutable en `PlanActivity` con su respectivo `user_id`, `accion` y `descripcion`.
- **Verificación:** Tras ejecutar cada acción colaborativa, existe un nuevo registro en `plan_activities` con los datos correspondientes.
