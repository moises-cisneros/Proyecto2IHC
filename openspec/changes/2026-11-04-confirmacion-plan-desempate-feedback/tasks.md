# Tareas: Tarea 7 — Confirmar Plan a partir de Votación, Resolución de Empates y Mejora por Feedback

## Sub-issue #46: [Feedback IHC] — Validación de fechas pasadas y confirmación explícita en selector de fechas (Refs #45)

- [x] 1.1 Crear `StorePlanRequest` y `UpdatePlanRequest` con regla `after_or_equal:today` y mensaje de error en español <!-- id: task-46-requests -->
- [x] 1.2 Actualizar `PlanesController::guardar` y `PlanesController::actualizar` para utilizar los nuevos Form Requests <!-- id: task-46-controller -->
- [x] 1.3 Agregar atributo `min` dinámico en `resources/views/planes/_modal_crear.blade.php` y `resources/views/planes/editar.blade.php` <!-- id: task-46-frontend-min -->
- [x] 1.4 Implementar botón de confirmación explícita «Aceptar fecha» con retención reactiva en `resources/views/planes/_sala_votacion.blade.php` <!-- id: task-46-aceptar-fecha -->
- [x] 1.5 Crear suite PHPUnit `tests/Feature/ValidacionFechasPlanTest.php` con al menos 4 casos de prueba (incluyendo seguridad) <!-- id: task-46-tests -->
- [x] 1.6 Ejecutar verificación con `scripts/verify-subissue.mjs 46`, crear PR y mergear a main <!-- id: task-46-pr -->

## Sub-issue #47: [Backend / Persistencia] — Migración, campos de opción ganadora, motivo de desempate y estado confirmado (Refs #45)

- [x] 2.1 Crear migración para añadir `propuesta_fecha_id`, `propuesta_lugar_id`, `motivo_desempate` y `fecha_confirmacion` en tabla `planes` <!-- id: task-47-migration -->
- [x] 2.2 Actualizar `$fillable`, casts y relaciones `propuestaFechaGanadora()` / `propuestaLugarGanadora()` en `app/Models/Plan.php` <!-- id: task-47-model-relations -->
- [x] 2.3 Implementar métodos de dominio `tieneEmpateFechas()`, `tieneEmpateLugares()`, `obtenerPropuestasEmpatadas()` y `obtenerGanadorIndiscutido()` en `Plan.php` <!-- id: task-47-model-methods -->
- [x] 2.4 Crear suite PHPUnit `tests/Unit/PersistenciaDesempateTest.php` con al menos 4 casos de prueba <!-- id: task-47-tests -->
- [x] 2.5 Ejecutar verificación con `scripts/verify-subissue.mjs 47`, crear PR y mergear a main <!-- id: task-47-pr -->

## Sub-issue #50: [Controlador / Lógica] — Endpoint de cierre, resolución de empate con motivo y autorización estricta (Refs #45)

- [x] 3.1 Definir habilidad `confirmar` en `app/Policies/PlanPolicy.php` para restringir el cierre al organizador en estado pendiente <!-- id: task-50-policy -->
- [x] 3.2 Crear Form Request `ConfirmarPlanRequest` con validación de pertenencia al plan y motivo obligatorio ($\ge 5$ caracteres) en empate <!-- id: task-50-request -->
- [x] 3.3 Implementar endpoint `POST /planes/{plan}/confirmar` en `routes/web.php` y acción en `PlanesController::confirmar` o controlador dedicado <!-- id: task-50-controller -->
- [x] 3.4 Crear suite PHPUnit `tests/Feature/ConfirmarPlanTest.php` con al menos 4 casos de prueba (incluyendo rechazo 403) <!-- id: task-50-tests -->
- [x] 3.5 Ejecutar verificación con `scripts/verify-subissue.mjs 50`, crear PR y mergear a main <!-- id: task-50-pr -->

## Sub-issue #48: [Interfaz / Blade] — Modal de desempate con motivo, banner de veredicto público y controles protegidos (Refs #45)

- [x] 4.1 Documentar nuevos componentes y especificaciones en `resources/DESIGN.md` antes de implementar en Blade <!-- id: task-48-design -->
- [x] 4.2 Agregar botón «Cerrar votación y confirmar» en sala bajo `@can('confirmar', $plan)` <!-- id: task-48-boton-cierre -->
- [x] 4.3 Construir modal nativo `<dialog>` de desempate con selector de opciones empatadas (Hick) y motivo obligatorio <!-- id: task-48-modal-dialog -->
- [x] 4.4 Implementar banner accesible de veredicto público con motivo del organizador en planes confirmados <!-- id: task-48-banner-veredicto -->
- [x] 4.5 Proveer acceso a la sala en `listar.blade.php` sin desbordar el layout de 800px y redimensionar avatar en `_cabecera.blade.php` <!-- id: task-48-ergonomia-fitts -->
- [x] 4.6 Crear suite PHPUnit `tests/Feature/VistasConfirmacionDesempateTest.php` con al menos 4 casos de prueba <!-- id: task-48-tests -->
- [ ] 4.7 Ejecutar verificación con `scripts/verify-subissue.mjs 48`, crear PR y mergear a main <!-- id: task-48-pr -->

## Sub-issue #49: [Pruebas y Evidencias] — Suite Playwright para escenario de desempate y documentación en evidencias/tarea-7.md (Refs #45)

- [ ] 5.1 Implementar suite E2E `e2e/feedback-1-selector-fechas.spec.ts` y capturar evidencia <!-- id: task-49-e2e-feedback1 -->
- [ ] 5.2 Implementar suite E2E `e2e/feedback-2-fechas-pasadas.spec.ts` y capturar evidencia <!-- id: task-49-e2e-feedback2 -->
- [ ] 5.3 Implementar suite E2E `e2e/tarea-7-confirmacion-desempate.spec.ts` para el escenario mínimo y capturar evidencias <!-- id: task-49-e2e-tarea7 -->
- [ ] 5.4 Redactar memoria formal en `evidencias/tarea-7.md` con enlaces, hashes de commits y galería de capturas <!-- id: task-49-evidencias -->
- [ ] 5.5 Ejecutar verificación con `scripts/verify-subissue.mjs 49`, crear PR y mergear a main <!-- id: task-49-pr -->
