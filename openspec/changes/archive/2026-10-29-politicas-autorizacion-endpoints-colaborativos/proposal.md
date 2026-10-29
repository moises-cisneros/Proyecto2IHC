# Propuesta: Políticas de autorización (PlanPolicy) y endpoints colaborativos

## Referencia
- **Sub-issue:** #8 — `[Controlador] — Políticas de autorización (PlanPolicy) y endpoints colaborativos (Refs #6)`
- **Issue Padre:** #6 — `[Requisito Docente] — Tarea 5: Funcionalidad colaborativa de votos y sugerencias`

## Contexto
El sistema Planazo requiere que la colaboración en planes grupales (invitar colaboradores, sugerir fechas/lugares y emitir votos) cuente con una capa de autorización desacoplada y robusta mediante Laravel Policies (`PlanPolicy`). Esta política debe diferenciar con precisión los privilegios del Creador (organizador), de los Participantes con invitación aceptada y de los usuarios Invitados/no autorizados, bloqueando cualquier acción no permitida con HTTP 403 Forbidden.

## Objetivos
1. Crear `app/Policies/PlanPolicy.php` para regular operaciones de visualización, edición, eliminación, cambio de estado, envío de invitaciones, proposición de opciones y votación.
2. Registrar la política en `app/Providers/AppServiceProvider.php` (o auto-descubrimiento nativo).
3. Implementar `app/Http/Controllers/ColaboracionController.php` (o controlador correspondiente) con los endpoints:
   - `POST /planes/{plan}/invitar` (exclusivo para el organizador/creador).
   - `POST /planes/{plan}/opciones` (creador y participantes con invitación aceptada).
   - `POST /opciones/{opcion}/votar` (creador y participantes con invitación aceptada).
4. Proteger `PlanesController` (métodos `eliminar` y `cambiarEstado`) para que participantes e invitados reciban 403 Forbidden si intentan alterar o eliminar planes del creador.
5. Desarrollar la suite `tests/Feature/ColaboracionEndpointsTest.php` con al menos 4 casos de prueba automatizados.
