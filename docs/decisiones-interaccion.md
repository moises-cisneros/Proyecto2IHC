# Decisiones de Interacción Ergonómica (IHC) — Planazo

Este documento registra las decisiones de diseño, usabilidad y ergonomía motriz y cognitiva implementadas en el módulo de **Historial de Actividad** de Planazo (Refs #1, Refs #14, Refs #15, Refs #16, Refs #17), evaluadas bajo los estándares de la disciplina de Interacción Humano-Computador (IHC).

---

## 1. Ergonomía Motriz: Ley de Fitts

### Formulación matemática
$$T = a + b \log_2\left(\frac{2D}{W}\right)$$

Donde:
* $T$: Tiempo de adquisición motriz del objetivo.
* $D$: Distancia inicial desde la posición del cursor/mano hacia el control interactivo.
* $W$: Ancho efectivo proyectado del objetivo en la dirección del movimiento.

### Problema / Línea Base (Alternativa Ingenua Descartada)
En la arquitectura heredada, el acceso a nuevas funcionalidades y botones de navegación secundaria carecía de un estándar de tamaño táctil: enlaces de texto plano con alturas efectivas de apenas $20\text{px}$ a $22\text{px}$ y zonas de pulsación reducidas. Conforme a la Ley de Fitts, reducir el ancho $W$ incrementa drásticamente el Índice de Dificultad ($ID = \log_2(2D/W)$), aumentando la tasa de errores de pulsación y la fatiga muscular del usuario, especialmente en dispositivos móviles y portátiles con trackpad.

### Decisión de Diseño Implementada
1. **Área táctil mínima $\ge 44\times 44\text{px}$:** Tanto en el enlace de navegación "Historial" en la barra superior (`resources/views/layouts/_partials/nav.blade.php`) como en los chips temáticos y botones de acción, se fijó una altura mínima `min-height: 44px` y anchura proporcional, cumpliendo con el estándar WCAG 2.5.5 (Target Size - Enhanced) y superando el umbral admisible WCAG 2.5.8 ($24\text{px}$).
2. **Ubicación en zona de fácil alcance:** La barra de navegación superior permite un acceso visual directo sin requerir navegación profunda en menús ocultos.
3. **Separación preventiva de acciones contiguas:** Se estableció un margen de separación (`gap: 10px`) entre chips y botones para evitar activaciones erróneas involuntarias.

### Impacto Cuantitativo
* **Reducción del Índice de Dificultad ($ID$):** Al duplicar el área de contacto efectiva $W$ de $22\text{px}$ a $44\text{px}$, el término $\log_2(2D/44) = \log_2(2D/22) - 1$, lo que representa una reducción de $1\text{ bit}$ en el $ID$ y una disminución estimada de ~**28%** en el tiempo de adquisición motriz $T$.

![Evidencia de Ley de Fitts en navegación](evidencias/historial/Fitts-despues.png)

---

## 2. Ergonomía de Selección: Ley de Hick

### Formulación matemática
$$T = b \log_2(n + 1)$$

Donde:
* $T$: Tiempo de reacción y decisión cognitiva del usuario.
* $n$: Número de alternativas equiprobables presentadas de forma simultánea.
* $b$: Constante empírica cognitiva (~$150\text{ms}$ a $200\text{ms}$ por bit).

### Problema / Línea Base (Alternativa Ingenua Descartada)
Una aproximación ingenua para filtrar un historial de eventos consiste en presentar un menú desplegable masivo o múltiples filtros combinados sin categorizar ($n \ge 12$ tipos de eventos dispersos: "plan creado", "plan editado", "nombre cambiado", "fecha actualizada", "organizador asignado", "estado pendiente", "estado confirmado", "estado cancelado", "invitación enviada", "voto emitido", etc.). Según la Ley de Hick, exponer una sobrecarga de alternativas desestructuradas dispara la latencia de decisión y genera parálisis por análisis.

### Decisión de Diseño Implementada
Se diseñó un selector temático de exactamente **$n = 4$ chips mutuamente excluyentes**:
1. **Todos:** Visión cronológica unificada del historial personal.
2. **Estados:** Focalizado exclusivamente en transiciones críticas (`cambio_estado`).
3. **Creaciones:** Eventos de alta relevancia de nuevos planes (`creacion`).
4. **Participación:** Eventos colaborativos y de interacción (`voto`, `invitacion`, etc.).

### Impacto Cuantitativo
* **Para la alternativa dispersa ($n = 12$):**
  $$T_{12} = b \log_2(12 + 1) = b \log_2(13) \approx 3.700\,b$$
* **Para el selector temático de $n = 4$ chips:**
  $$T_4 = b \log_2(4 + 1) = b \log_2(5) \approx 2.322\,b$$
* **Beneficio medible:**
  $$\Delta T = \frac{3.700 - 2.322}{3.700} \approx \mathbf{37.24\%}\text{ de reducción en latencia de decisión cognitiva.}$$

Adicionalmente, cada chip incluye retroalimentación de estado mediante `aria-pressed="true|false"` para accesibilidad por lectores de pantalla.

![Evidencia de Ley de Hick en selector temático](evidencias/historial/Hick-despues.png)

### Evidencias de los Filtros Temáticos en la Aplicación

#### Filtro por Estados
![Filtro Estados](evidencias/historial/FiltroEstado.png)

#### Filtro por Creaciones
![Filtro Creaciones](evidencias/historial/FiltroCreaciones.png)

#### Filtro por Participación
![Filtro Participación](evidencias/historial/FiltroParticipacion.png)

---

## 3. Teoría de la Carga Cognitiva y Reconocimiento (Nielsen H6)

### Fundamento Teórico
La memoria de trabajo humana posee una capacidad finita limitada a $7 \pm 2$ unidades o chunks de información concurrentes (Miller, 1956; Sweller, 1988). El principio heurístico H6 de Nielsen ("Reconocimiento antes que recuerdo") postula que la interfaz debe hacer visibles los objetos, acciones y opciones para que el usuario no tenga que memorizar información de un paso a otro.

### Problema / Línea Base (Alternativa Ingenua Descartada)
Listados crudos de auditoría tipo log técnico ("Registro ID #142 actualizado a las 11:00") fuerzan al usuario a retener en memoria qué plan se modificó, qué significaba el cambio o a tener que hacer clic para abrir modales secundarios solo para entender el evento.

### Decisión de Diseño: Tarjeta `ActivityCard` en 4 Dimensiones Explícitas
El componente Blade [`<x-activity-card />`](../resources/views/components/activity-card.blade.php) estructura cada evento en exactamente **4 dimensiones semánticas auto-contenidas**:

1. **Qué ocurrió:** Tipo de acción con badge visual (`badge-accion`) y descripción legible en lenguaje natural (ej. "Cambio de estado", "Se confirmó la logística del viaje").
2. **Cuándo ocurrió:** Marca temporal relativa amigable (`diffForHumans()`) y fecha absoluta formateada accesible (`<time datetime="...">`).
3. **Cuál fue el resultado:** Badge explícito del estado resultante (`badge-resultado`) con codificación cromática (Verde para `Confirmado`, Rojo para `Cancelado`, Ámbar para `Pendiente`).
4. **Qué elemento involucró:** Metadatos claros del plan (`elemento-plan`) y autor del evento (`elemento-usuario`).

### Impacto Ergonómico
* **Cero sobrecarga de memoria:** La tarjeta es auto-explicativa; no requiere navegación adicional ni esfuerzo de deducción mental.
* **Carga extrínseca minimizada:** El usuario reconoce de inmediato la severidad y el resultado de la acción gracias a los tokens cromáticos definidos en `DESIGN.md`.

![Evidencia de Carga Cognitiva y ActivityCard](evidencias/historial/CargaCognitiva-despues.png)

### Evidencia de la Interfaz con Actividades
![Historial con actividades registradas](evidencias/historial/HistorialConActividades.png)

### Evidencia del Estado Vacío Ilustrado
![Estado vacío ilustrado](evidencias/historial/HistorialVacio.png)

---

## 4. Estado del Arte y Verificación Automatizada

| Componente | Principio Evaluado | Criterio de Éxito | Estado |
|---|---|---|---|
| `nav.blade.php` | Ley de Fitts | Área táctil $\ge 44\times 44\text{px}$, etiqueta `aria-label` | ✅ Verificado |
| Selector de chips | Ley de Hick | Exactamente $n=4$ alternativas, $\Delta T \approx -37\%$ | ✅ Verificado |
| `ActivityCard` | Carga Cognitiva (H6) | 4 dimensiones explícitas, auto-contenido | ✅ Verificado |
| `HistorialVistaTest` | Automatización | 5 casos de prueba Feature pasando al 100% | ✅ Verificado |
| `HistorialConsultaTest` | Aislamiento | 6 casos de prueba Feature pasando al 100% | ✅ Verificado |
| `PlanActivityTest` | Persistencia | 5 casos de prueba Unit pasando al 100% | ✅ Verificado |
