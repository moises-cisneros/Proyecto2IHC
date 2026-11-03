# Tasks: Hardening de Autenticación, Integridad Referencial y Auditoría Completa

## Sub-issue 1: Backend / Persistencia (#30)

- [x] 1.1 Crear migración de clave foránea en `planes.user_id` con `cascadeOnDelete()`
- [x] 1.2 Implementar método `planes(): HasMany` en `App\Models\User`
- [x] 1.3 Eliminar campo editable `organizador` en `PlanesController::actualizar` y agregar badge de solo lectura en `resources/views/planes/editar.blade.php`
- [x] 1.4 Blindar máquina de estados en `PlanesController::cambiarEstado` contra reactivación de planes cancelados
- [x] 1.5 Crear suite de pruebas `tests/Feature/IntegridadPlanesTest.php` (4 casos de prueba) y validar con PHPUnit en Docker

## Sub-issue 2: Controlador / Seguridad (#31)

- [ ] 2.1 Implementar `$request->session()->invalidate()` y `$request->session()->regenerateToken()` en `AutentificacionController::salir`
- [ ] 2.2 Unificar respuesta genérica neutra en `AutentificacionController::enviarCorreo` para neutralizar ataques de enumeración
- [ ] 2.3 Proteger rutas de acceso con middleware `guest` y aplicar rate limiting en `routes/web.php`
- [ ] 2.4 Crear suite de pruebas `tests/Feature/HardeningAutenticacionTest.php` (4 casos de prueba) y validar con PHPUnit en Docker

## Sub-issue 3: Historial / Auditoría (#32)

- [ ] 3.1 Registrar eventos de `invitacion` en `ColaboracionController::invitar`
- [ ] 3.2 Registrar eventos de `propuesta` en `ColaboracionController::guardarOpcion`
- [ ] 3.3 Registrar eventos de `voto` en `ColaboracionController::votar`
- [ ] 3.4 Registrar eventos de desvinculación en `ColaboracionController::abandonar`
- [ ] 3.5 Crear suite de pruebas `tests/Feature/TrazabilidadColaboracionTest.php` (4 casos de prueba) y validar con PHPUnit en Docker

## Cierre y Archivo del SDD

- [ ] 4.1 Ejecutar suite completa de pruebas PHPUnit (`69+` tests) y script de verificación `scripts/verify-subissue.mjs`
- [ ] 4.2 Sincronizar especificaciones en `openspec/specs/` y archivar el cambio en `openspec/changes/archive/`
