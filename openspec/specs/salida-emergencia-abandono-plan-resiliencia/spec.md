# Especificación: Salida de emergencia y abandono de plan

## Requisitos

### Criterios de Aceptación
1. **Endpoint `POST /planes/{plan}/abandonar`:**
   - Exclusivo para participantes con invitación aceptada.
   - Si un usuario no participante o ajeno intenta invocarlo, retorna 403 Forbidden.
2. **Validación del Creador:**
   - El creador/organizador del plan no puede abandonarlo; si lo intenta, recibe 403 Forbidden.
3. **Persistencia e Integridad de Datos:**
   - La membresía del usuario se elimina de la tabla `invitaciones` (o se desvincula).
   - El plan y las participaciones de los demás usuarios permanecen intactos.
   - Todos los votos previamente emitidos por el participante en las opciones de ese plan se eliminan.
4. **Experiencia de Usuario (H3 & Fitts):**
   - Modal `<dialog>` de confirmación con desambiguación léxica.
   - Botones con `min-h-[44px]` y separación antagónica de al menos 12px (`gap-3`).
