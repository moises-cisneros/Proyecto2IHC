# Unificación del sistema de diseño (DESIGN.md como fuente de verdad)

Branch: main (sin commits; cambios staged/unstaged por el usuario) · Delivery: ask-on-risk · Runner: `docker compose exec -T planazo_app php artisan test` + `pnpm run build`

## Specs

- S1: «Agregaste nuevos componentes en design.md, pero esos componentes no son especificos o no dicen a quien le pertenece?, ademas son reutilizables en otras partes del codigo»
- S2: «procura no agregar cosas innecesarias o si agregaste y cumplen una funcion en alguna parte, es mejor reemplazar esa parte con este nuevo componente u algo similar»
- S3: «el archivo de diseño debe ser la base, porque a partir de ahi vamos a regenerar los componentes en figma»
- S4: «revisa si los archivos de token, app, componentes, vistas, entre otros estan alineados y no refutan en lo que se realizan»
- S5: «si, has todo lo que falte y unificalos.» (campo de formulario + estilos inline restantes)
- S6 (derivado, regla vigente de DESIGN.md): sin `style="..."` estático; solo tokens; sin transiciones; interactivos ≥ `--touch-min`.

## Tasks

- [x] T1 (S1-S4) Tarjeta/Avatar/Métrica/Badge globales + tokens avatar — inline
- [x] T2 (S5) Campo de formulario único `.formulario` + `.campo` (acceso, cuenta, editar, modal_crear) — inline
- [x] T3 (S5,S6) Inline styles `planes/_gastos_liquidacion` — delegated (writer A)
- [x] T4 (S5,S6) Inline styles `planes/_sala_votacion` + `detalle` — delegated (writer B)
- [x] T5 (S5,S6) Inline styles `analitica/index`, `components/activity-card`, `planes/historial` — delegated (writer C)
- [x] T6 (S5,S6) Inline styles `planes/_cabecera`, `_participantes`, `_tabla_seccion`, `listar` — delegated (writer D)
- [x] T7 (S3,S4) Merge CSS fragments en app.css, actualizar DESIGN.md, test guardián de `style=`, suite + build

Route declaration: T3-T6 delegated por Writer trigger (a): 4 unidades independientes, superficies de edición disjuntas, cientos de líneas cada una.

## Log

L1 (2026-11-16): Agregaste nuevos componentes en design.md, pero esos componentes no son especificos o no dicen a quien le pertenece?, ademas son reutilizables en otras partes del codigo que falta agregar algo similar, puedes revisar el repo, procura no agregar cosas innecesarias o si agregaste y cumplen una funcion en alguna parte, es mejor reemplazar esa parte con este nuevo componente u algo similar, asi mismo el archivo de diseño debe ser la base, porque a partir de ahi vamos a regenerar los componentes en figma, por lo que revisa el contexto en engram de como se maneja el archivo de diseño, revisa si los archivos de token, app, componentes, vistas, entre otros estan alineados y no refutan en lo que se realizan.
L2 (2026-11-16): si, has todo lo que falte y unificalos.
L3: T1 y T2 hechos. Suite 254 verde tras T1. Campo unificado = radio md + 44px (decisión del agente, recomendada por Fitts).
L4: T3-T7 hechos. Suite 257 verde, build OK, 6 style= dinámicos restantes (analitica width). Tests de Fitts reescritos para leer CSS.
L5: Segunda pasada: tokens de layout/media/tipografía, diálogos y banners globales, .form-reset, clases muertas removidas, guard test ampliado (259 tests OK).
