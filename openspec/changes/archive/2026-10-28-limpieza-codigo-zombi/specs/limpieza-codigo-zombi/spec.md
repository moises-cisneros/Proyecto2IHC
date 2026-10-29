# Spec Delta: Limpieza de Código Zombi y Evidencias Históricas

## Capability: `backend-clean-architecture`

### Requirement: Eliminación de modelos muertos
El sistema no debe mantener definiciones de modelos Eloquent vacías o en desuso que confundan la arquitectura del backend.

#### Scenario: Ausencia de modelo Autentificacion
- **Given** que el backend utiliza `App\Models\User` para la autenticación y persistencia de usuarios
- **When** se inspecciona el directorio `app/Models/`
- **Then** el archivo `app/Models/Autentificacion.php` no debe existir
- **And** ninguna clase ni controlador de la aplicación debe importar `App\Models\Autentificacion`

### Requirement: Centralización de evidencias docentes
Todas las evidencias históricas y capturas de entregas previas deben ubicarse en `docs/evidencias/` para evitar dispersión en la raíz del repositorio.

#### Scenario: Evidencias de la clase del 13 de octubre
- **Given** las capturas y notas de la clase 13 previamente en la raíz (`Tarea de clases 13-oct/`)
- **When** se accede a `docs/evidencias/clase-13/`
- **Then** se encuentran las capturas `1.png`, `2.png`, `3.png` y el archivo `tarea en clases.md`
- **And** el directorio `Tarea de clases 13-oct/` en la raíz ya no existe

