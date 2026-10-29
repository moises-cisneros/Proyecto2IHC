# Design: Limpieza de Código Zombi y Evidencias Históricas

## Contexto y Motivación
En el diagnóstico arquitectónico (`docs/internal/AnalisisArquitecturaYOptimizacion.md` e `InformeTarea5.md`), se identificó que el modelo `app/Models/Autentificacion.php` era un archivo zombi creado durante las primeras clases que quedó vacío con solo un comentario `//`. La lógica real de autenticación reside en `AutentificacionController` interactuando con el modelo estándar `App\Models\User`.

Asimismo, la carpeta `Tarea de clases 13-oct/` permanecía en la raíz del proyecto mientras que las evidencias posteriores se fueron ubicando en `docs/evidencias/clase17/`.

## Decisiones Técnicas
1. **Borrado de `app/Models/Autentificacion.php`**:
   - Se elimina de forma permanente.
   - No se toca la tabla `autentificacions` en la migración original para preservar el historial de migraciones inalterado y evitar inconsistencias en bases de datos ya migradas.
2. **Reubicación de `Tarea de clases 13-oct/`**:
   - Se traslada todo su contenido (`1.png`, `2.png`, `3.png`, `tarea en clases.md`) a `docs/evidencias/clase-13/`.
   - Se mantiene la estructura ya usada por `docs/evidencias/clase17/`.

## Verificación de Impacto
- Grep general en el proyecto asegura que no hay llamadas a `App\Models\Autentificacion`.
- Rutas en `routes/web.php` continúan apuntando a `AutentificacionController` sin alteraciones.
- Las suites de pruebas existentes (`FiltradoPlanesTest`) y las de autenticación futuras no se ven afectadas.

