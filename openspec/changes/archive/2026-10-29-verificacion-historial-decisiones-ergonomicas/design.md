# Design: Verificación de Historial y Documentación de Decisiones Ergonómicas

## 1. Contexto y Objetivos

El objetivo de este diseño es cerrar formalmente el requisito docente (Refs #1) para el módulo de auditoría de historial en Planazo mediante:
- Auditoría técnica y formalización de las decisiones ergonómicas en `docs/decisiones-interaccion.md`.
- Generación y custodia de evidencias gráficas antes/después en `docs/evidencias/historial/`.
- Verificación automatizada de resiliencia de código sin regresiones.

## 2. Marco Matemático y Formulación en Documentación

### 2.1 Ley de Fitts: Ergonomía Motriz
- $T = a + b \log_2(2D/W)$
- Parámetros: $W_1 = 22\text{px}$ vs $W_2 = 44\text{px}$.
- Variación: $\Delta ID = -1\text{ bit} \implies \Delta T \approx -28\%$.

### 2.2 Ley de Hick: Tiempo de Decisión
- $T = b \log_2(n+1)$
- Parámetros: $n_1 = 12$ vs $n_2 = 4$.
- Variación: $T(12) \approx 3.70\,b$ vs $T(4) \approx 2.32\,b \implies \Delta T \approx -37.24\%$.

### 2.3 Teoría de la Carga Cognitiva y Reconocimiento (H6)
- Estructuración en 4 dimensiones de `ActivityCard` (Qué ocurrió, Cuándo, Resultado, Elemento involucrado).
- Auto-contención de la información para evitar retención en memoria de trabajo ($7 \pm 2$).

## 3. Matriz de Evidencias Gráficas

| Evidencia | Archivo | Principio |
|---|---|---|
| Navegación Historial | `docs/evidencias/historial/Fitts-despues.png` | Ley de Fitts ($W \ge 44\text{px}$) |
| Chips de Categoría | `docs/evidencias/historial/Hick-despues.png` | Ley de Hick ($n = 4$) |
| Tarjeta ActivityCard | `docs/evidencias/historial/CargaCognitiva-despues.png` | Carga Cognitiva / Nielsen H6 |

## 4. Verificación de Suites de Pruebas

Suites integradas:
1. `tests/Unit/PlanActivityTest.php` (5 tests)
2. `tests/Feature/HistorialConsultaTest.php` (6 tests)
3. `tests/Feature/HistorialVistaTest.php` (5 tests)
