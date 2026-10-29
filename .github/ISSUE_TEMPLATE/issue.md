---
name: Issue / Requisito General
about: Plantilla unificada para consignas docentes, funcionalidades o auditorías IHC
title: '[Tipo] — [Nombre del requisito, funcionalidad o evaluación]'
labels: ['requisito']
assignees: ''
---

## Consigna / Objetivo
<!-- Copia la consigna del docente o describe el objetivo principal de este requerimiento -->

## Historia de Usuario / Flujo
<!-- Contexto de uso o flujo evaluado -->
**Como** [rol del usuario]  
**Quiero** [acción o necesidad]  
**Para** [beneficio o valor esperado]  

## Criterios de Aceptación

- [ ] **CA-01:**
- [ ] **CA-02:**
- [ ] **CA-03:**

## Consideraciones de Interacción / IHC (Opcional según la entrega)
<!-- Si la consigna exige IHC: detallar principios aplicados (Fitts, Hick, Carga Cognitiva) o lista de heurísticas (H1-H10) -->
- **Decisión / Principio:**

## Sub-issues Atómicos
<!-- Descomposición obligatoria en mínimo 3 sub-issues por capas arquitectónicas.
     Puede extenderse a 4 o más si la consigna requiere un sub-issue separado de pruebas/evidencias.
     IMPORTANTE: La issue madre permanece ABIERTA hasta la revisión docente final. -->
- [ ] # [Capa 1: Backend / Persistencia]
- [ ] # [Capa 2: Controlador / Lógica de negocio]
- [ ] # [Capa 3: Interfaz / Blade / Accesibilidad]
- [ ] # [Capa 4 (Opcional): Pruebas automatizadas y documentación de evidencias]

> **Vinculación jerárquica en GitHub (Árbol nativo):**  
> `gh api -X POST /repos/:owner/:repo/issues/:padre/sub_issues -F sub_issue_id=:id_global`
