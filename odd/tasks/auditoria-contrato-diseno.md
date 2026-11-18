# Auditoría y contrato de diseño (Figma ⇄ código)

Objetivo: dejar un contrato intermedio (App Map, Task Flows y pantallas) del que partan Figma y el repo, tras auditar el código actual.
Rama: `refactor/auditoria-contrato-diseno` · Entrega: `ask-on-risk` · Runner: `php artisan test` (PHPUnit), Playwright en `e2e/`.

## Specs

- **S1** — Crear la skill de patrones oscuros, general al proyecto y no solo de una tarea.
- **S2** — «investiga primero el endpoint abandonar, si hay controladores o codigo que hacen lo mismo con otro nombre procura eliminar y refactorizarlo».
- **S3** — «si a las skills les hace falta actualizar con todas las decisiones que se tomaron debes hacerlo antes de ejecutarlas».
- **S4** — Aplicar `interaction-decisions`, `heuristic-evaluation`, `refactoring`, `e2e-testing`, `information-architecture`, `design-system-audit` a todo el repo y registrar el resultado.
- **S5** — El contrato (App Map y Task Flows) debe construirse desde el inventario verificado, no desde el README. _(Suposición: ubicación del contrato aún sin decidir; mientras tanto el README es la fuente corregida.)_
- **S6** — «Si, corrige todo y actualiza todo, pero no hagas push ni PRs, ni commits.» (aprobación del cambio de `cambiarEstado`).

## Tasks

- [x] T1 · S1 · inline · skill `dark-patterns` + registro en `AGENTS.md` y `CONTRIBUTING.md` — commit `6d5b652`
- [x] T2 · S2 · inline · eliminar `abandonar`, unificar membresía, estados muertos — commit `79717fc`
- [x] T3 · S3 · inline · decisiones añadidas a las skills — commit `6d5b652` y segunda actualización (sin commit)
- [x] T4 · S4 · inline · informe `docs/internal/Informes/AuditoriaPantallasYFlujos.md`
- [x] T5 · S5 · inline · README: App Map (11 pantallas) separado de Task Flows (TF1–TF11)
- [x] T6 · S4/S6 · inline · R3: `cambiarEstado` solo cancela; «Confirmar» del listado enlaza a la sala; `confirmar` admite plan sin propuestas de fecha; 267 tests PHPUnit verdes
- [x] T7 · S4/S6 · inline · H2–H5 corregidos (control deshabilitado, `data-type="danger"`, logo `x-icono`, `aria-current` en chips)
- [x] T8 · S4/S6 · inline · E2E: specs desactualizados corregidos (`clase-21`, `tarea-6`, `tarea-7`, `tarea-8`, `tarea-9`, `tarea-10`), helper `e2e/helpers/gastos.ts` y 3 specs nuevos (`recuperar`, `editar`, `historial`); los 14 specs pasan
- [x] T8b · S4/S6 · inline · H11 modalidad de pago sin preselección por JS; H12 enlaces `pestana` → `tab`; H13 test vacío corregido — 271 tests PHPUnit verdes
- [x] T9 · S5 · inline · contrato `resources/FLOWS.md` (App Map, 4.x composición de pantallas, matriz de permisos, TF1–TF11, trazabilidad, inventario de 36 rutas) + `FlowsContractTest`; referencias en README, DESIGN.md, AGENTS.md, CONTRIBUTING.md y skills
- [x] T9b · S4/S6 · inline · H14–H17: Rechazar junto a Aceptar en el listado, Editar deshabilitado omitido, gap de acciones 12 px, README corregido — 281 tests PHPUnit verdes
- [x] T10 · commits por unidades de trabajo estructuradas según CONTRIBUTING.md — commits `6b40bbc`, `c03011e`, `8b2ba56`, `e446658`, `ba0cf1e`, `b5d16c6`
- [ ] T11 · Figma: un frame por pantalla (ids de FLOWS.md §2.1) y variantes de DESIGN.md

## Log

- **L1 (usuario, 2026-11-17):** «investiga primero el endpoint abandonar, si hay controladores o codigo que hacen lo mismo con otro nombre, procura eliminar y refactorizarlo, porque puede que hagan lo mismo solo que con otro nombre. sigue con la auditoria y aplica las skills a todo el repo: /interaction-decisions /heuristic-evaluation /refactoring /e2e-testing /information-architecture /design-system-audit, asi mismo si a las skills les hace falta actualizar con todas las decisiones que se tomaron debes hacerlo antes de ejecutarlas.»
- **L2 (usuario, 2026-11-17):** `abandonar` no validaba deuda ni estado: era un atajo, no un alias. Eliminado.
- Hallazgo R3: `cambiarEstado` con `confirmado` evitaba votación y desempate. Al retirarlo se descubrió que `confirmar` rechazaba planes sin propuestas de fecha (el modal prometía conservar la fecha límite); se corrigió la regla para no dejar planes sin poder confirmarse.
- Hallazgo adicional: `aria-pressed` en enlaces del historial (ARIA inválido); sustituido por `aria-current`.
- E2E: varios specs estaban desactualizados desde el refactor de UI; los fallos por `page.goto` tras editar archivos son del Vite con polling en Docker, y el login tiene `throttle:5,1`.
- Hallazgos del e2e: la modalidad de pago se preseleccionaba por JS (patrón oscuro 4.1) y los enlaces «Liquidar» usaban un parámetro de URL inexistente (`pestana`); ambos tenían tests que pasaban por vacío.
- **L3 (usuario, 2026-11-17):** «Agrega un archivo donde este el app map y los flujos, para que asi ahi este detallado. Ademas corrige los hallazgos que encontraste.»
- Contrato creado en `resources/FLOWS.md`; se verificó con una mutación que `FlowsContractTest` detecta rutas faltantes. Al contrastar con el código se corrigieron además: «Aceptar» sin «Rechazar» en el listado, «Editar» deshabilitado mudo, separación de 8 px en acciones y la afirmación errónea «gastos solo en plan confirmado».
- Riesgo: ítems 2 y 3 (ruta pública eliminada, policies tocadas, contrato de `cambiarEstado` restringido); suite completa PHPUnit y Pint en verde.
- **L4 (usuario, 2026-11-18):** «Realiza los commits correspondientes con @CONTRIBUTING.md». Se organizaron y ejecutaron los commits atómicos por unidades de trabajo (comprobantes, vía única de confirmación, contrato FLOWS.md, accesibilidad y sistema de diseño, E2E y evidencias, pipeline de CI y skills).
