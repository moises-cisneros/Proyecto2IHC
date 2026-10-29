# Proposal: Limpieza de Código Zombi y Evidencias Históricas

## Issue Reference
- Sub-issue: Refs #2 (Closes #2)
- Requisito Docente / Madre: Refs #1

## Why
El repositorio heredado contiene archivos residuales y carpetas históricas en la raíz (`Tarea de clases 13-oct/` y `app/Models/Autentificacion.php`). Este modelo está completamente vacío y nunca se utiliza (la autenticación se gestiona con `User` y `AutentificacionController`). Para mantener la arquitectura limpia (Clean Architecture / PSR-12) y cumplir con `AGENTS.md` y `docs/internal/InformeTarea5.md`, es necesario sanear el árbol de archivos sin alterar rutas, controladores ni migraciones.

## What Changes
- Eliminar el archivo zombi y vacío `app/Models/Autentificacion.php`.
- Reubicar la carpeta `Tarea de clases 13-oct/` a `docs/evidencias/clase-13/`.
- Verificar que no existan referencias rotas en rutas, controladores, migraciones y vistas.

## Capabilities

### New Capabilities
None.

### Modified Capabilities
- `backend-clean-architecture`: Eliminación de archivos zombis y ordenamiento estructurado de evidencias docentes en `docs/evidencias/`.

## Impact
- `app/Models/`: Queda exclusivamente con los modelos activos de dominio (`Plan`, `User`).
- `docs/evidencias/`: Centraliza el histórico de entregas (`clase-13` y `clase17`).
- Cero impacto negativo o breaking changes en la base de datos o endpoints web.

