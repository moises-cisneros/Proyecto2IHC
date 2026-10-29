# verificacion-historial-decisiones-ergonomicas Specification

## Purpose
Documentar la auditoría ergonómica, formalización matemática y verificación de pruebas automatizadas del módulo de historial de actividad en Planazo bajo los estándares de IHC.

## Requirements

### Requirement: Documentación matemática de la Ley de Fitts
El documento `docs/decisiones-interaccion.md` debe justificar la reducción del índice de dificultad motriz en la navegación y controles del historial.

#### Scenario: Formulación y contraste de Fitts
- **Given** los controles interactivos de navegación (`nav.blade.php`) y chips
- **When** se evalúa la dimensión efectiva de pulsación ($W \ge 44\text{px}$) frente a la línea base previa ($W = 22\text{px}$)
- **Then** se formula $T = a + b \log_2(2D/W)$
- **And** se demuestra una reducción de 1 bit en el Índice de Dificultad y una disminución de ~28% en tiempo motriz

### Requirement: Documentación matemática de la Ley de Hick
El documento debe formalizar la reducción de latencia cognitiva de decisión mediante la agrupación en $n=4$ categorías temáticas.

#### Scenario: Formulación y contraste de Hick
- **Given** el selector temático de chips en `historial.blade.php`
- **When** se evalúan las $n=4$ alternativas temáticas frente a la opción dispersa no agrupada ($n=12$)
- **Then** se formula $T = b \log_2(n + 1)$
- **And** se demuestra una reducción del 37.24% en el tiempo de procesamiento cognitivo de decisión

### Requirement: Justificación de Carga Cognitiva y Reconocimiento (Nielsen H6)
El documento debe justificar la estructura auto-contenida de `ActivityCard` en sus 4 dimensiones para evitar sobrecargar la memoria de trabajo ($7 \pm 2$).

#### Scenario: Descomposición en 4 dimensiones de evento
- **Given** una tarjeta de evento en el historial
- **When** se inspecciona su contenido visual
- **Then** explicita qué ocurrió, cuándo ocurrió, cuál fue el resultado y qué elemento involucró
- **And** elimina la necesidad de navegar a vistas secundarias para comprender el evento

### Requirement: Integración de evidencias gráficas
La documentación debe referenciar las capturas de pantalla de la interfaz ubicadas en `docs/evidencias/historial/`.

#### Scenario: Referencia a evidencias en el documento
- **Given** el directorio `docs/evidencias/historial/`
- **When** se consulta `docs/decisiones-interaccion.md`
- **Then** incluye enlaces válidos a las capturas correspondientes a Fitts, Hick y Carga Cognitiva
