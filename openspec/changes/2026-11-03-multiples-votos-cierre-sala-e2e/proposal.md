# Propuesta: Múltiples Votos, Cierre de Sala Colaborativa y Pruebas E2E de Flujo Humano

## Referencia
- **Issue Madre:** #33 — `[Requisito] — Tarea 6: Múltiples Votos, Cierre de Sala Colaborativa y Pruebas E2E de Flujo Humano`
- **Sub-issues Asociadas:**
  - #34 — `[Backend / Dominio] — Regla de negocio para votos múltiples y congelamiento de sala cerrada (Refs #33)`
  - #35 — `[Interfaz / Blade] — Sala colaborativa cerrada, prevención de errores (H5), directivas @can y refinamiento estético (Refs #33)`
  - #36 — `[E2E / IHC] — Suite Playwright en modo visible basada en tareas humanas y matriz de evidencias (Refs #33)`
  - #37 — `[Evidencias & Matriz] — Capturas antes/después de la feature, auditoría visual y de accesibilidad, y matriz en evidencias/tarea-6.md (Refs #33)`

## Contexto
En la versión actual de Planazo, un participante puede emitir un voto en una opción mediante toggle. Sin embargo, para cumplir con el estándar docente de la Tarea 6, se requiere:
1. Permitir formalmente la votación múltiple (aprobación simultánea en varias fechas y/o lugares en un plan abierto).
2. Congelar estrictamente la votación y la proposición de nuevas opciones cuando la sala pasa a estado `confirmado`, `cancelado` o su fecha límite expira, bloqueando las mutaciones tanto a nivel de Policy (`403 Forbidden`) como a nivel de interfaz (Nielsen H5: Prevención de errores).
3. Mantener habilitada la capacidad de invitar nuevos colaboradores a planes `confirmados` (para sumar personas a planes ya acordados), bloqueando las invitaciones únicamente en planes `cancelados`.
4. Mejorar la ergonomía de selección (H5) en la sala colaborativa (campo estructurado `<input type="datetime-local">` para fechas) y reparar la botonera de acciones en el listado de planes (disposición horizontal de 44px según la Ley de Fitts).
5. Crear y validar una suite E2E de Playwright narrada como tareas humanas («Anita se loguea...», «Anita vota por varias opciones...», «Anita verifica congelamiento en sala cerrada»), acompañada de su matriz de evidencias en `evidencias/tarea-6.md`.

## Objetivos
- **Sub-issue #34 (Backend / Dominio):**
  - Actualizar `PlanPolicy.php` para que `votar` y `proponer` solo estén permitidos si el plan está en estado `pendiente` y no vencido (`$plan->fecha_limite >= now()->startOfDay()`).
  - Asegurar que `invitar` esté permitido en planes `pendiente` y `confirmado`, y denegado en `cancelado`.
  - Crear suite PHPUnit `tests/Feature/ReglasColaboracionCierreTest.php` con al menos 4 casos de prueba.
- **Sub-issue #35 (Interfaz / Blade):**
  - Implementar banner visual de sala cerrada en `resources/views/planes/_sala_votacion.blade.php`.
  - Deshabilitar botones de voto y ocultar botón de proponer en salas confirmadas/cerradas.
  - Alternar inputs en el modal de propuestas según tipo (`datetime-local` para fechas, `text` para lugares).
  - Corregir la botonera de `resources/views/planes/listar.blade.php` a disposición horizontal ergonómica (`min-height: 44px`, `gap: 8px`).
  - Crear suite PHPUnit `tests/Feature/SalaColaborativaCerradaTest.php` con al menos 4 casos de prueba.
- **Sub-issue #36 (E2E / IHC):**
  - Crear `e2e/tarea-6-varios-votos.spec.ts` cubriendo login con seeder, votos múltiples reactivos, toggle de voto, verificación de sala cerrada y auditoría `@axe-core/playwright`.
- **Sub-issue #37 (Evidencias & Matriz):**
  - Registrar capturas antes/después y redactar la matriz comparativa en `evidencias/tarea-6.md`.
