# Propuesta: Salida de emergencia (H3), abandono de plan y suite de resiliencia

## Referencia
- **Sub-issue:** #10 — `[Pruebas y Resiliencia] — Salida de emergencia (H3), abandono de plan y suite de resiliencia (Refs #6)`
- **Issue Padre:** #6 — `[Requisito Docente] — Tarea 5: Funcionalidad colaborativa de votos y sugerencias`

## Contexto
En interfaces colaborativas es fundamental garantizar el principio heurístico de Nielsen H3 (Control y libertad del usuario), ofreciendo una "salida de emergencia" clara y segura. Si un usuario ha sido invitado a un plan y decide no participar más, debe poder desvincularse (abandonar el plan) de manera autónoma, sin depender del creador, sin eliminar el plan para los demás integrantes y asegurando la consistencia de la base de datos al remover automáticamente sus votos emitidos. Asimismo, el organizador/creador no debe poder "abandonar" su propio plan (debe cancelarlo o transferirlo), evitando estados huérfanos.

## Objetivos
1. Definir la habilidad de autorización `abandonar` en `app/Policies/PlanPolicy.php`.
2. Implementar el endpoint `POST /planes/{plan}/abandonar` en `app/Http/Controllers/ColaboracionController.php`.
3. Implementar la lógica de abandono:
   - Restringido a participantes con invitación aceptada.
   - Bloquear al creador si intenta abandonar (403 Forbidden).
   - Eliminar la membresía/invitación del usuario en el plan.
   - Limpiar de forma atómica todos los votos previos del participante saliente en las opciones del plan.
4. Integrar el botón accesible de "Abandonar plan" con confirmación de diálogo nativo `<dialog>` en `resources/views/planes/_participantes.blade.php` o `_cabecera.blade.php`.
5. Crear y validar la suite de pruebas `tests/Feature/AbandonoPlanTest.php` con al menos 4 casos de prueba.
