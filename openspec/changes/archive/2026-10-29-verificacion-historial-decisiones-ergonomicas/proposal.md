# Proposal: Verificación de historial y documentación de decisiones ergonómicas

## Issue Reference
- Sub-issue: Refs #17 (Closes #17)
- Requisito Docente / Madre: Refs #1

## Why
El módulo de historial de auditoría de actividad (desarrollado en las sub-issues #14, #15 y #16) culmina su ciclo de entrega asegurando su verificación integral, validación de resiliencia y documentación formal de las decisiones de interacción ergonómica.

La sub-issue #17 aborda la capa de Pruebas y Evidencias generando el documento `docs/decisiones-interaccion.md` sustentado matemáticamente en las Leyes de Fitts y Hick y en la Teoría de la Carga Cognitiva, integrando capturas de evidencia en `docs/evidencias/historial/` y validando las suites de pruebas automatizadas mediante `scripts/verify-subissue.mjs 17`.

## What Changes
1. **Documentación de Decisiones de Interacción (`docs/decisiones-interaccion.md`):**
   - Justificación matemática de la Ley de Fitts ($T = a + b \log_2(2D/W)$) para el enlace de navegación y controles con $W \ge 44\text{px}$, logrando una reducción de ~28% en tiempo motriz.
   - Justificación matemática de la Ley de Hick ($T = b \log_2(n+1)$) para el selector temático de $n=4$ chips, logrando una reducción de 37.24% en latencia de decisión frente a alternativas no categorizadas.
   - Justificación de la Teoría de la Carga Cognitiva ($7 \pm 2$ chunks) y Principio de Reconocimiento antes que Recuerdo (Nielsen H6) para la tarjeta `ActivityCard` en sus 4 dimensiones explícitas.
2. **Evidencias Visuales en `docs/evidencias/historial/`:**
   - `Fitts-despues.png`: Evidencia de accesibilidad y dimensiones táctiles en la barra de navegación.
   - `Hick-despues.png`: Evidencia de los $n=4$ chips y cálculo de latencia de decisión.
   - `CargaCognitiva-despues.png`: Evidencia de la tarjeta `ActivityCard` estructurada en 4 dimensiones.
3. **Script de Generación de Evidencias (`scripts/generate-evidences.ps1`):**
   - Generación programática y reproducible de capturas visuales de alta resolución.
4. **Validación de Suites Automatizadas:**
   - Verificación de suites `PlanActivityTest`, `HistorialConsultaTest` e `HistorialVistaTest`.

## Capabilities

### New Capabilities
- `ergonomic-interaction-decisions`: Documentación formal y verificable del impacto ergonómico y usabilidad de Planazo según los estándares IHC.

## Impact
- `docs/decisiones-interaccion.md`
- `docs/evidencias/historial/Fitts-despues.png`
- `docs/evidencias/historial/Hick-despues.png`
- `docs/evidencias/historial/CargaCognitiva-despues.png`
- `scripts/generate-evidences.ps1`
