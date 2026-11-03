# Tareas: Tarea 6 — Múltiples Votos, Cierre de Sala y Pruebas E2E

## Sub-issue #34: [Backend / Dominio] — Regla de negocio para votos múltiples y congelamiento de sala cerrada
- [x] 1.1 Actualizar habilidades `votar` y `proponer` en `app/Policies/PlanPolicy.php` para exigir `$plan->estado === 'pendiente'` y fecha límite no vencida <!-- id: task-34-policy-votar -->
- [x] 1.2 Verificar que `invitar` en `PlanPolicy.php` esté habilitado en `pendiente` y `confirmado`, y denegado en `cancelado` <!-- id: task-34-policy-invitar -->
- [x] 1.3 Verificar autorización `Gate::authorize` y trazabilidad en `ColaboracionController` <!-- id: task-34-controller -->
- [x] 1.4 Crear suite PHPUnit `tests/Feature/ReglasColaboracionCierreTest.php` con al menos 4 casos de prueba <!-- id: task-34-tests -->
- [x] 1.5 Ejecutar suite completa, verificar con `scripts/verify-subissue.mjs 34` y crear PR para #34 <!-- id: task-34-pr -->

## Sub-issue #35: [Interfaz / Blade] — Sala colaborativa cerrada, prevención de errores (H5), directivas @can y refinamiento estético
- [x] 2.1 Refactorizar modal de propuestas en `_sala_votacion.blade.php` con selector e input `<input type="datetime-local">` para fechas (H5) <!-- id: task-35-input-h5 -->
- [x] 2.2 Agregar banner de sala cerrada y deshabilitar botones de voto en planes confirmados (`@can`/`@cannot`) <!-- id: task-35-banner-cierre -->
- [x] 2.3 Permitir invitar en planes confirmados pero ocultar en cancelados en `_participantes.blade.php` <!-- id: task-35-invitar-confirmado -->
- [x] 2.4 Corregir botonera de acciones en `resources/views/planes/listar.blade.php` a disposición horizontal ergonómica (Fitts 44px) <!-- id: task-35-botonera-fitts -->
- [x] 2.5 Crear suite PHPUnit `tests/Feature/SalaColaborativaCerradaTest.php` con al menos 4 casos de prueba <!-- id: task-35-tests -->
- [x] 2.6 Ejecutar suite completa, verificar con `scripts/verify-subissue.mjs 35` y crear PR para #35 <!-- id: task-35-pr -->

## Sub-issue #36: [E2E / IHC] — Suite Playwright en modo visible basada en tareas humanas y matriz de evidencias
- [x] 3.1 Crear prueba E2E `e2e/tarea-6-varios-votos.spec.ts` estructurada como tareas humanas («Anita...») para votos múltiples y toggle reactivo <!-- id: task-36-e2e-multiples -->
- [x] 3.2 Añadir caso de prueba E2E para comprobación de inmutabilidad en sala confirmada/cerrada <!-- id: task-36-e2e-cerrada -->
- [x] 3.3 Integrar auditoría de accesibilidad con `@axe-core/playwright` en la sala colaborativa <!-- id: task-36-e2e-axe -->
- [x] 3.4 Validar ejecución en modo normal y headed (`pnpm run test:e2e:headed`) <!-- id: task-36-e2e-verify -->
- [x] 3.5 Crear PR para #36 <!-- id: task-36-pr -->

## Sub-issue #37: [Evidencias & Matriz] — Capturas antes/después de la feature, auditoría visual y de accesibilidad, y matriz en evidencias/tarea-6.md
- [ ] 4.1 Capturar y almacenar evidencias de la suite antes y después de la feature en `docs/evidencias/tarea-6/` <!-- id: task-37-capturas -->
- [ ] 4.2 Redactar y consolidar la matriz comparativa en `evidencias/tarea-6.md` <!-- id: task-37-matriz -->
- [ ] 4.3 Sincronizar especificaciones en `openspec/specs/` y archivar el cambio en `openspec/changes/archive/` <!-- id: task-37-archive -->
- [ ] 4.4 Crear PR para #37 <!-- id: task-37-pr -->
