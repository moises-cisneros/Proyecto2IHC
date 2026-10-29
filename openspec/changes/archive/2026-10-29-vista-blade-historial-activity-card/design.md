# Design: Vista Blade de Historial con Tarjetas ActivityCard y Filtros Accesibles

## 1. Contexto y Objetivos

El objetivo de este diseño es proveer la interfaz de usuario para el historial de actividad en Planazo (sub-issue #16, Refs #1) fundamentada en principios de Interacción Humano-Computador (IHC):
- **Ley de Fitts ($T = a + b \log_2(2D/W)$):** Enlaces y controles con área de pulsación mínima $\ge 44\times 44$px para reducir el índice de dificultad motriz en desktop y dispositivos táctiles.
- **Ley de Hick ($T = b \log_2(n+1)$):** Selector temático acotado a $n=4$ alternativas cognitivas (`Todos`, `Estados`, `Creaciones`, `Participación`), optimizando el tiempo de procesamiento de opciones.
- **Reducción de Carga Cognitiva ($7 \pm 2$):** Tarjetas de actividad organizadas en 4 dimensiones coherentes y explícitas (Qué ocurrió, Cuándo, Resultado y Elemento involucrado), evitando ambigüedad interpretativa.
- **Feedback del Sistema y Prevención de Errores (Nielsen H1, H3, H10):** Iconografía, estados vacíos ilustrados y navegación contextual clara.

## 2. Arquitectura de Interfaz

### 2.1 Componente `ActivityCard` (`resources/views/components/activity-card.blade.php`)

Estructura de 4 dimensiones:
```html
<article class="activity-card" aria-label="Tarjeta de actividad: ...">
    <!-- Dimensión 1: Qué ocurrió -->
    <span class="badge-accion">{{ $etiquetaAccion }}</span>
    <!-- Dimensión 3: Cuál fue el resultado -->
    <span class="badge-resultado">Resultado: {{ $estado }}</span>
    <!-- Dimensión 2: Cuándo ocurrió -->
    <time datetime="...">{{ $tiempoRelativo }}</time>
    <!-- Descripción -->
    <p class="descripcion-actividad">{{ $descripcion }}</p>
    <!-- Dimensión 4: Qué elemento involucró -->
    <div class="meta-elemento">
        <span>Plan involucrado: {{ $plan }}</span>
        <span>Autor: {{ $usuario }}</span>
    </div>
</article>
```

Tokens visuales (alineados con `DESIGN.md`):
- `creacion`: Fondo verde tenue `#edf7ef`, borde `#c2e2c8`, texto `#25663a`.
- `cambio_estado`: Fondo azul tenue `#eff6ff`, borde `#bfdbfe`, texto `#1d4ed8`.
- `actualizacion`: Fondo amarillo tenue `#fefce8`, borde `#fef08a`, texto `#854d0e`.
- `participacion`: Fondo púrpura tenue `#faf5ff`, borde `#e9d5ff`, texto `#6b21a8`.

### 2.2 Barra de Navegación (`resources/views/layouts/_partials/nav.blade.php`)

Extraída modularmente desde `layouts/app.blade.php` e integrando:
- Enlace "Mis Planes"
- Enlace "Historial" (con estado activo condicional y dimensiones táctiles Fitts $\ge 44$px)
- Perfil de usuario y botón "Salir"

### 2.3 Selector de Chips Temáticos y Estado Vacío Ilustrado

- Chips temáticos con indicación accesible `aria-pressed`.
- Estado vacío con ilustración SVG temática, encabezado amigable y botón de redirección hacia `planes.listar`.

## 3. Estrategia de Pruebas Automatizadas

Suite `tests/Feature/HistorialVistaTest.php` (PHPUnit):
1. `test_vista_historial_renderiza_correctamente_para_usuario_autenticado`: Valida status 200, título y estructura principal.
2. `test_vista_muestra_enlace_a_historial_en_barra_de_navegacion`: Verifica enlace accesible en barra de navegación.
3. `test_tarjeta_actividad_muestra_las_cuatro_dimensiones_explicitas`: Comprueba la presencia de las 4 dimensiones en el HTML renderizado.
4. `test_selector_de_chips_muestra_las_cuatro_categorias_de_hick`: Valida los 4 chips y atributo de accesibilidad `aria-pressed`.
5. `test_vista_muestra_estado_vacio_ilustrado_cuando_no_hay_actividades`: Comprueba el estado vacío ilustrado.
