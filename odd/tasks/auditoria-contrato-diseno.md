# Auditoría y contrato de diseño (Figma ⇄ código)

Objetivo: dejar un contrato intermedio (App Map, Task Flows y pantallas) del que partan Figma y el repo, tras auditar el código actual.
Rama: `refactor/auditoria-contrato-diseno` · Entrega: `ask-on-risk` · Runner: `php artisan test` (PHPUnit), Playwright en `e2e/`.

## Specs

- **S1** — Crear la skill de patrones oscuros, general al proyecto y no solo de una tarea.
- **S2** — «investiga primero el endpoint abandonar, si hay controladores o codigo que hacen lo mismo con otro nombre procura eliminar y refactorizarlo».
- **S3** — «si a las skills les hace falta actualizar con todas las decisiones que se tomaron debes hacerlo antes de ejecutarlas».
- **S4** — Aplicar `interaction-decisions`, `heuristic-evaluation`, `refactoring`, `e2e-testing`, `information-architecture`, `design-system-audit` a todo el repo y registrar el resultado.
- **S5** — El contrato (App Map y Task Flows) debe construirse desde el inventario verificado, no desde el README. *(Suposición: ubicación del contrato aún sin decidir.)*

## Tasks

- [x] T1 · S1 · inline · skill `dark-patterns` + registro en `AGENTS.md` y `CONTRIBUTING.md`
- [x] T2 · S2 · inline · eliminar `abandonar`, unificar membresía (`deUsuario`, `esParticipanteAceptado`), estados muertos — 260 tests verdes
- [x] T3 · S3 · inline · decisiones añadidas a las 5 skills existentes y a `dark-patterns`
- [x] T4 · S4 · inline · informe `docs/internal/Informes/AuditoriaPantallasYFlujos.md`
- [ ] T5 · S5 · corregir App Map y separar Task Flows (bloqueado: decidir ubicación del contrato)
- [ ] T6 · S4 · decidir R3 (`cambiarEstado` → `confirmado`) y aplicar
- [ ] T7 · S4 · specs E2E faltantes: `recuperar`/`restablecer`, `editar`, `historial`

## Log

- **L1 (usuario, 2026-11-17):** «investiga primero el endpoint abandonar, si hay controladores o codigo que hacen lo mismo con otro nombre, procura eliminar y refactorizarlo, porque puede que hagan lo mismo solo que con otro nombre. sigue con la auditoria y aplica las skills a todo el repo: /interaction-decisions /heuristic-evaluation /refactoring /e2e-testing /information-architecture /design-system-audit, asi mismo si a las skills les hace falta actualizar con todas las decisiones que se tomaron debes hacerlo antes de ejecutarlas.»
- `abandonar` no validaba deuda ni estado: era un atajo, no un alias. Eliminado.
- Hallazgo R3: `cambiarEstado` con `confirmado` evita votación y desempate; tests dependen de ello. Pendiente de decisión.
- Riesgo: ítem 2 y 3 (ruta pública eliminada, policies tocadas); suite completa y Pint en verde.
