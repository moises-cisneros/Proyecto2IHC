# Design: Suite de Pruebas de Autenticación y Rutas Protegidas

## Contexto y Motivación
El diagnóstico arquitectónico documentado en `docs/internal/InformeTarea5.md` evidenció que, si bien el controlador `AutentificacionController.php` y las rutas de autenticación estaban funcionales en el proyecto recibido, no existía ninguna prueba automatizada que asegurara su comportamiento. Cualquier refactorización en middlewares, controladores o políticas de acceso corría el riesgo de introducir regresiones silenciosas o brechas de seguridad.

La sub-issue #5 subsana esta carencia estableciendo la suite `tests/Feature/AutentificacionTest.php`.

## Decisiones Técnicas

### 1. Marco y Estrategia de Pruebas
- **Framework:** PHPUnit / Laravel Testing Tools integrados en Laravel 12.
- **Tipo de Test:** `Feature` (`tests/Feature/`), ejecutando peticiones HTTP reales contra el kernel de la aplicación para validar respuestas, redirecciones, sesiones y estado de base de datos.
- **Aislamiento de Estado:** Uso del trait `Illuminate\Foundation\Testing\RefreshDatabase` para garantizar que cada prueba se ejecute en un entorno de base de datos limpio e independiente.
- **Bypass CSRF:** Exclusión del middleware `ValidateCsrfToken` en `setUp()` para permitir peticiones POST directas en las pruebas sin requerir token sintético.

### 2. Estructura de la Suite (`tests/Feature/AutentificacionTest.php`)
La clase extiende `Tests\TestCase` y organiza los siguientes casos de prueba:

1. **`test_usuario_no_autenticado_es_redirigido_al_intentar_acceder_a_mis_planes`:**
   - Envía `GET /mis-planes` sin sesión.
   - Aserción: `assertRedirect('/ingresar')` (HTTP 302 hacia login).

2. **`test_usuario_puede_iniciar_sesion_con_credenciales_correctas`:**
   - Crea un usuario con `User::factory()` y contraseña hasheada.
   - Envía `POST /ingresar` con campos `correo` y `contrasena`.
   - Aserciones: `assertRedirect('/mis-planes')` y `assertAuthenticatedAs($user)`.

3. **`test_login_falla_con_credenciales_invalidas_sin_revelar_campo_especifico`:**
   - Crea un usuario de prueba y envía una contraseña errónea.
   - Aserciones: `assertSessionHas('error', 'El correo o la contraseña no son correctos.')` y `assertGuest()`.
   - Justificación de seguridad: Evita la enumeración de cuentas o revelación de existencia de usuarios.

4. **`test_registro_valida_correo_unico_y_contrasena_minima_8_caracteres`:**
   - Registra previamente un correo existente y envía `POST /registro` con contraseña corta (`123`) y correo duplicado.
   - Aserciones: `assertSessionHasErrors(['correo', 'contrasena'])` y `assertGuest()`.

5. **`test_usuario_puede_registrarse_correctamente_e_iniciar_sesion_automaticamente`:**
   - Envía datos válidos a `POST /registro`.
   - Aserciones: `assertRedirect('/mis-planes')`, `assertAuthenticated()` y `assertDatabaseHas('users', ['email' => ...])`.

6. **`test_usuario_autenticado_puede_cerrar_sesion`:**
   - Autentica a un usuario mediante `actingAs($user)` y envía `POST /salir`.
   - Aserciones: `assertRedirect('/ingresar')` y `assertGuest()`.

### 3. Ejecución y Entorno
- La suite se ejecuta en el contenedor Docker oficial (`planazo_app`) mediante:
  ```bash
  docker compose exec -T app php artisan test tests/Feature/AutentificacionTest.php
  ```
- No requiere dependencias externas ni librerías adicionales.
