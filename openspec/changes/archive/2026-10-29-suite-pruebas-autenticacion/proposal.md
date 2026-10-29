# Proposal: Suite de Pruebas de Autenticación y Rutas Protegidas

## Issue Reference
- Sub-issue: Refs #5 (Closes #5)
- Requisito Docente / Madre: Refs #1

## Why
El módulo de autenticación implementado en `app/Http/Controllers/AutentificacionController.php` gestiona el acceso al sistema (`POST /ingresar`), registro de usuarios (`POST /registro`), cierre de sesión (`POST /salir`) y protección de rutas mediante el middleware `auth` (`/mis-planes`, `/planes`, etc.).

Sin embargo, el repositorio heredado carecía de una suite formal de pruebas automatizadas que blindara estos flujos críticos contra regresiones. La sub-issue #5 exige saldar esta deuda técnica implementando una suite integral en PHPUnit (`tests/Feature/AutentificacionTest.php`) que valide al menos 4 casos de prueba clave:
1. Interceptación y redirección 302 hacia `/ingresar` para usuarios no autenticados en rutas protegidas.
2. Autenticación exitosa con credenciales válidas y regeneración de identificador de sesión.
3. Rechazo de credenciales erróneas con mensaje genérico de error (sin revelar si falló el correo o la clave).
4. Reglas de validación en registro (unicidad de correo, longitud mínima de 8 caracteres y confirmación de contraseña).

Adicionalmente, para garantizar una cobertura exhaustiva del ciclo de vida de autenticación, se incorporan casos para el registro exitoso con logueo directo y el cierre de sesión (`POST /salir`).

## What Changes
1. **Suite de Pruebas Feature (`tests/Feature/AutentificacionTest.php`):**
   - Implementación y consolidación de 6 casos de prueba automatizados con aislamiento de base de datos (`RefreshDatabase`) y bypass de token CSRF (`ValidateCsrfToken`).
2. **Casos de Prueba Automatizados:**
   - `test_usuario_no_autenticado_es_redirigido_al_intentar_acceder_a_mis_planes`: Verifica respuesta HTTP 302 y cabecera `Location: /ingresar`.
   - `test_usuario_puede_iniciar_sesion_con_credenciales_correctas`: Verifica redirección a `/mis-planes` y aserción `assertAuthenticatedAs`.
   - `test_login_falla_con_credenciales_invalidas_sin_revelar_campo_especifico`: Verifica que no se cree sesión (`assertGuest`) y que exista el flash `error` genérico en sesión.
   - `test_registro_valida_correo_unico_y_contrasena_minima_8_caracteres`: Verifica fallos de validación en los campos `correo` y `contrasena`.
   - `test_usuario_puede_registrarse_correctamente_e_iniciar_sesion_automaticamente`: Verifica creación en tabla `users`, inicio de sesión automático y redirección a `/mis-planes`.
   - `test_usuario_autenticado_puede_cerrar_sesion`: Verifica que `POST /salir` desautentique al usuario y redirija a `/ingresar`.
3. **Validación y Auditoría:**
   - Ejecución de pruebas en Docker mediante `docker compose exec -T app php artisan test tests/Feature/AutentificacionTest.php`.
   - Actualización de `docs/internal/InformeTarea5.md` y checklist de Issue #1.

## Capabilities

### New Capabilities
- `authentication-test-suite`: Suite automatizada de pruebas de integración HTTP para validación de autenticación, registro, cierre de sesión y middleware `auth` en Laravel 12.

## Impact
- `tests/Feature/AutentificacionTest.php`: Archivo de pruebas Feature en PHPUnit.
- `docs/internal/InformeTarea5.md`: Registro del estado y cobertura de la deuda técnica saldada.
