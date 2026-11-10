# ODD Task: Refactorización UI e Íconos SVG (Issue #61)

## Estado General

- **Issue Madre:** #61 — `[Refactor] — Componentes de íconos SVG nativos, eliminación de CSS inline y desacoplamiento de vistas`
- **Sub-issues:**
  - #62: `[Interfaz] — Componente Blade de íconos SVG nativos accesibles y tokens en app.css`
  - #63: `[Interfaz] — Migración de emojis a SVGs y sustitución de inline styles en vistas de planes y sala`
  - #64: `[Testing] — Verificación de no-regresión en suite PHPUnit y validación de selectores semánticos`

---

## Tareas Atómicas

### 1. Sub-issue #62 (Capa 1: Componentes y Estilos)

- [x] Branch: `refactor/issue-62-componente-icono-svg`
- [x] TDD: Crear `tests/Feature/ComponenteIconoTest.php`
- [x] Componente Blade `resources/views/components/icono.blade.php`:
  - Variantes: `calendario`, `ubicacion`, `bloqueado`, `check`, `editar`, `eliminar`, `dinero`, `pago` (y aliases).
  - Trazados con `viewBox="0 0 24 24"`, `stroke="currentColor"`, `fill="none"`, `aria-hidden="true"`.
- [x] Clases en `public/css/app.css`:
  - `.boton-accion-sm`: $\ge 44\text{ px}$ (Fitts), padding compacto, centrado.
  - `.boton-accion-md`: $\ge 44\text{ px}$ (Fitts), ancho uniforme 104px para botones de tabla.
- [x] Ejecutar PHPUnit + Formateador.
- [x] PR y merge a `main` cerrando #62 (PR #70).

### 2. Sub-issue #63 (Capa 2: Vistas y Erradicación de Emojis/CSS Inline)

- [x] Branch: `refactor/issue-63-migracion-vistas-inline-styles`
- [x] TDD: Crear `tests/Feature/VistasPlanesIconosTest.php`
- [x] Refactorizar `_cabecera.blade.php`: sustituir emoji `🚫` por `<x-icono nombre="bloqueado" />`.
- [x] Refactorizar `_sala_votacion.blade.php`: sustituir emojis (`📅`, `📍`, `✓`, `⚖`) por `<x-icono>`.
- [x] Refactorizar `_tabla_seccion.blade.php`:
  - Reemplazar botones con `style="min-height: 44px; width: 104px; ..."` por `.boton-accion-md`.
  - Reemplazar SVGs inline duplicados por `<x-icono nombre="editar" />` y `<x-icono nombre="eliminar" />`.
- [x] Ejecutar PHPUnit + Formateador.
- [x] PR y merge a `main` cerrando #63 (PR #71).

### 3. Sub-issue #64 (Capa 3: Verificación y No-Regresión)

- [x] Branch: `test/issue-64-testing-no-regresion`
- [x] Ejecutar suite completa (100% verde).
- [x] Ejecutar `node scripts/verify-subissue.mjs 64`.
- [x] Ejecutar `node scripts/format.mjs --check`.
- [ ] PR y merge a `main` cerrando #64.
