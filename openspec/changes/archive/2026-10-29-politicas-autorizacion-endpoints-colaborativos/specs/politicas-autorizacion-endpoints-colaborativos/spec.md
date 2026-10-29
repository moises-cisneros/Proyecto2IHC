# Especificación: Políticas de autorización y endpoints colaborativos

## Requisitos
El sistema debe garantizar la autorización en todas las operaciones del flujo colaborativo y restringir la mutación de planes exclusivamente a los usuarios autorizados.

### Criterios de Aceptación
1. **Política de Plan (`PlanPolicy`):**
   - Creador puede invitar, actualizar, eliminar, cambiar estado, proponer opciones y votar.
   - Participante con invitación aceptada puede ver el plan, proponer opciones y votar en las opciones.
   - Invitados pendientes o usuarios sin invitación reciben 403 Forbidden al proponer opciones o votar.
   - Participantes e invitados no pueden eliminar ni cambiar el estado del plan.
2. **Endpoint `POST /planes/{plan}/invitar`:**
   - Accesible únicamente por el creador del plan.
   - Valida el correo y genera un token único para la invitación.
3. **Endpoint `POST /planes/{plan}/opciones`:**
   - Accesible por creador y participantes aceptados.
   - Registra una nueva opción de fecha o lugar.
4. **Endpoint `POST /opciones/{opcion}/votar`:**
   - Accesible por creador y participantes aceptados en el plan correspondiente.
   - Registra o conmuta el voto del usuario autenticado.
5. **Protección de `PlanesController`:**
   - Intentos no autorizados de borrado o cambio de estado retornan 403 Forbidden.
