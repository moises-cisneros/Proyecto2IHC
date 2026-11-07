# DESIGN.md — Sistema de Diseño y Guía de Interfaz de Planazo

> **Fuente de verdad visual y ergonómica de Planazo.**  
> Este documento abstrae los estilos reales del proyecto ([`public/css/app.css`](../public/css/app.css) y [`resources/css/app.css`](css/app.css)) para garantizar coherencia, accesibilidad y apego a los principios de Interacción Humano-Computador (IHC).  
> **Regla de oro:** No implementar estilos al azar ni agregar librerías externas superfluas; reutilizar y extender los tokens aquí definidos.

---

## 1. Principios Generales de Diseño

1. **Minimalismo e Inteligibilidad (Sin librerías superfluas):** Emplear los elementos nativos de HTML5 (`<dialog>`, `<button>`, `<input>`) y clases de utilidad de Tailwind CSS v4 / `app.css`. No introducir frameworks visuales externos ni scripts pesados innecesarios.
2. **Consistencia Visual:** Toda pantalla (autenticación, listado, formularios y sala de votación) comparte la misma paleta cálida y esquema de bordes y píldoras.
3. **Ergonomía Motriz (Ley de Fitts):** Todo botón o control interactivo frecuente debe tener un área táctil mínima de **44×44px** (`min-h-[44px]` o `py-2.5 px-4`). Controles antagónicos (ej. Confirmar vs Cancelar/Eliminar) deben mantener una separación mínima de **8px a 12px** (`gap-3`).
4. **Claridad de Decisión (Ley de Hick):** Agrupar opciones cuando superen 5 elementos (p. ej. en la sala de votación: pestaña "Fechas" vs "Lugares").
5. **Accesibilidad y Feedback (Nielsen H1 y H6):**
   * El estado nunca se comunica solo con color: siempre acompaña texto explícito e ícono semántico.
   * Contraste AA verificado para todo texto contra su fondo.
   * El sistema reconoce la sesión activa (`auth()->user()`) en lugar de exigir recordar o tipear datos redundantes.

---

## 2. Paleta de Colores y Tokens

Abstraída de [`public/css/app.css`](../public/css/app.css):

| Token semántico | Valor Hex / RGB | Uso específico en la aplicación |
| :--- | :--- | :--- |
| `background` | `#f6f6f4` | Fondo general de la página (`body`) |
| `surface` / `card` | `#ffffff` | Fondo de tarjetas, tablas, modales `<dialog>` y formularios |
| `foreground` | `#1c1c1a` | Texto principal de alta legibilidad |
| `muted` | `#6f6f6a` | Texto de apoyo, encabezados de tabla, etiquetas secundarias |
| `border` | `#e3e3df` | Bordes de inputs, tablas, separadores y tarjetas |
| `brand-bar` | `#ffe683` | Amarillo cálido de la barra superior de navegación y chips activos |
| `brand-hover` | `#ffd93d` | Estado hover de la barra y chips interactivos |
| `primary` | `#60605f` | Botón primario de acción (`.boton`), píldora con texto `#ffffff` |
| `primary-hover` | `#4a4a49` | Hover del botón primario |
| `secondary` | `#ffffff` | Botón secundario (`.boton-claro`) con borde `#e3e3df` y texto `#1c1c1a` |
| `accent-edit` | `#a3a60b` | Botón de edición (`.boton-editar`), oliva/amarillo verdoso |
| `destructive` | `rgb(185, 14, 19)` / `#c0392b` | Botón eliminar (`.boton-rojo`), mensajes de error y alertas |
| `success` | `#25663a` | Texto de confirmación, estado «confirmado» y alertas de éxito |
| `success-bg` | `#edf7ef` | Fondo de alerta y chip de estado «confirmado» |
| `pending` | `#8a6100` | Texto de estado «pendiente» |
| `pending-bg` | `#fff4d6` | Fondo de chip de estado «pendiente» |
| `canceled` | `#6f6f6a` | Texto de estado «cancelado» |
| `canceled-bg` | `#eeeeec` | Fondo de chip de estado «cancelado» |

---

## 3. Semántica de Estados y Roles

### A. Estados del Plan

| Estado | Chip de Presentación | Comportamiento e Interacción |
| :--- | :--- | :--- |
| `pendiente` | Fondo `#fff4d6`, texto `#8a6100` | El plan está abierto a cambios o votación. Permite confirmar o cancelar. |
| `confirmado` | Fondo `#edf7ef`, texto `#25663a` | Plan definido. **No se puede eliminar directamente** (primero debe cancelarse). |
| `cancelado` | Fondo `#eeeeec`, texto `#6f6f6a` | Plan cancelado. **No puede volver a confirmarse**. Permite eliminación definitiva. |

### B. Estados del Proceso de Pago y Liquidación (Entidad `Pago`)

> **Reutilización estricta de tokens:** Este nuevo proceso no inventa estilos adicionales; reutiliza los chips de color semántico existentes en la paleta oficial.

| Estado del Pago | Chip de Presentación | Consecuencia en el Negocio y Saldos |
| :--- | :--- | :--- |
| `pendiente` | Fondo `#fff4d6`, texto `#8a6100` | El deudor declaró el pago. **Consecuencia:** **NO reduce la deuda** ni altera saldos. Espera revisión del receptor. |
| `confirmado` | Fondo `#edf7ef`, texto `#25663a` | El receptor validó el cobro. **Consecuencia:** **Reduce la deuda y actualiza saldos netos**. Inmutable: no puede aplicarse dos veces (idempotencia). |
| `rechazado` | Fondo `#fbeceb`, texto `#c0392b` | El receptor rechazó el pago. **Consecuencia:** Exige motivo obligatorio de rechazo. **La deuda permanece intacta**. |

### C. Roles de Usuario

| Rol | Presentación | Permisos de Interacción |
| :--- | :--- | :--- |
| `Organizador / Creador` | Badge sólido neutro (`bg-[#60605f] text-white`) | Puede invitar, proponer opciones, cancelar o eliminar el plan. |
| `Participante Aceptado` | Badge con contorno (`border border-[#e3e3df] bg-white`) | Puede proponer fechas/lugares, votar y abandonar el plan (H3). **No puede borrar el plan**. |
| `Invitado Pendiente` | Badge atenuado (`bg-[#eeeeec] text-[#6f6f6a]`) | Solo lectura hasta aceptar o rechazar la invitación. |

---

## 4. Tipografía y Jerarquía

* **Fuente base:** `system-ui, "Segoe UI", Roboto, sans-serif` (respaldada por `'Instrument Sans'` en `@theme` de Tailwind v4).
* **Escala tipográfica:**
  * **Marca del Navbar:** `50px`, `font-weight: 700`, color `#1c1c1a`.
  * **Títulos de Pantalla (`h1`):** `24px` a `28px`, `font-weight: 700`.
  * **Títulos de Diálogo / Sección (`h2`):** `18px` a `20px`, `font-weight: 600`.
  * **Títulos de Tarjeta / Opción:** `16px`, `font-weight: 600`.
  * **Cuerpo de texto general:** `15px`, `line-height: 1.5`, color `#1c1c1a`.
  * **Textos de apoyo, fechas e insignias:** `13px`, color `#6f6f6a`.

---

## 5. Espaciado y Formas Geométricas

* **Estilo Píldora (`rounded-full` / `border-radius: 50px`):**
  * Aplicado consistentemente a: botones (`.boton`), inputs de búsqueda y filtros, selectores y chips de filtro.
* **Estilo Tarjeta / Diálogo (`rounded-xl` / `border-radius: 12px`):**
  * Aplicado a: modales `<dialog>`, tarjetas de plan, contenedores de detalle y sala de votación.
* **Contenedor Principal:**
  * Ancho máximo: `max-w-[1024px]` centrado horizontalmente (`margin: 0 auto;`), padding `24px 16px`. Adaptado para alojar tablas modulares de 5 columnas con grupos de acciones ergonómicos de 260px sin desbordamiento.

---

## 6. Patrones de Componentes

### Botones

* **Primario (`.boton`):** Fondo `#60605f`, texto blanco, bordes redondeados (`50px`), padding `9px 16px`. Área táctil mínima de 44px. Un solo botón primario por sección.
* **Secundario (`.boton-claro`):** Fondo blanco, borde `#e3e3df`, texto `#1c1c1a`.
* **Destructivo (`.boton-rojo`):** Fondo `rgb(185, 14, 19)`, reservado exclusivamente para la confirmación de eliminación o abandono.
* **Estado deshabilitado (`:disabled`):** Opacidad `0.4`, cursor `not-allowed`.

### Diálogo Modal Nativo (`<dialog>`)

* **Reemplazo integral:** Sustituye a SweetAlert2 para **confirmaciones bloqueantes** mediante el componente nativo `resources/views/components/dialog-confirm.blade.php`.
* **Dimensiones y Forma:** Ancho máximo `400px`, padding `24px`, bordes redondeados `12px` (`rounded-xl`), fondo blanco `#ffffff` y backdrop oscuro `rgba(0, 0, 0, 0.4)`.
* **Cero CDNs externos:** Se controla con JavaScript puro nativo (`showModal()` y `close()`), asegurando compatibilidad 100% offline y sin retardos artificiales.
* **Separación de Botones (Fitts):** Botón de confirmación y botón de cancelación alineados al final con separación mínima de `12px` (`gap-3`) y altura táctil ≥ 44 px.

### Notificaciones Flotantes Nativas (Toast Auto-Dismissable)

* **Reemplazo integral de alertas tipo Toast de SweetAlert2:** Resuelve el problema donde las alertas flash estáticas quedaban fijas al tope de la pantalla empujando el contenido hacia abajo (layout shift) sin desaparecer.
* **Posicionamiento Flotante:** `position: fixed; top: 20px; right: 20px; z-index: 50;`. Se renderiza por encima de la vista sin desplazar la estructura del documento (*Zero Cumulative Layout Shift*).
* **Temporizador y Desvanecimiento Automático:** Se muestra con una animación suave de entrada (`fade-in`), permanece visible durante **3 segundos** (3000ms) y desaparece automáticamente con transición de desvanecimiento (`fade-out`) antes de removerse del DOM.
* **Control del Usuario (Nielsen H3):** Dispone de un botón visible de cierre manual `×` con área táctil accesible (≥ 44 px) y etiqueta `aria-label="Cerrar notificación"` para descarte inmediato opcional.
* **Accesibilidad (Nielsen H1):** Contenedor accesible con `role="status"` o `role="alert"` y directiva `aria-live="polite"` para lectores de pantalla.
* **Tokens Visuales del Toast:**
  * Éxito: Fondo `#edf7ef`, borde `#c2e2c8`, texto `#25663a`, ícono de verificación.
  * Error: Fondo `#fbeceb`, borde `#f5c6cb`, texto `#c0392b`, ícono de advertencia.
* **Componente y API Global en `ui.js`:**
  * Componente Blade: `resources/views/components/toast-alerta.blade.php` para mensajes de sesión (`session('exito')` / `session('error')`).
  * Función cliente global: `window.mostrarToast({ tipo: 'exito' | 'error', mensaje: '...' })`.

### Panel de Participantes (`_participantes.blade.php`)

* **Lista de Miembros:** Bloques limpios con avatar/inicial, nombre del participante y badge de estado (`Aceptado` o `Pendiente`).
* **Distinción de Roles:**
  * Organizador: Badge sólido `#60605f` con texto blanco.
  * Participante: Badge con contorno `#e3e3df` y texto neutro `#1c1c1a`.
* **Acción de Invitar (Fitts):** Botón visible «Invitar participante» situado en la cabecera del panel con altura táctil mínima de 44px (`min-h-[44px]`).

### Sala de Votación (`_sala_votacion.blade.php`)

* **Divulgación Progresiva y Ley de Hick** ($n \le 4$) — Pestañas o selector de categoría para alternar entre «Fechas propuestas» y «Lugares propuestos», impidiendo la sobrecarga cognitiva de listas mezcladas.
* **Tarjeta de Opción Autónoma:**
  * Tarjeta blanca con borde `#e3e3df` y radio de `12px`.
  * Nombre de la fecha/lugar propuesto y nombre de quién lo sugirió en tipografía secundaria (`13px`, `#6f6f6a`).
  * Badge de votos acumulados en color ámbar (`bg-[#fff4d6] text-[#8a6100] font-semibold`).
  * Botón de voto primario accesible («Votar» / «Quitar voto») con altura mínima de 44px.
* **Separación Antagónica:** Si el usuario propuso esa opción y desea retirarla, el botón de eliminar se separa por al menos `12px` (`gap-3`) del botón de votar para evitar toques accidentales.

### Salida de Emergencia (Nielsen H3 — Abandono de Plan)

* **Botón de Abandono:** Botón secundario con texto «Abandonar plan» visible exclusivamente para participantes invitados.
* **Confirmación Preventiva:** Abre el modal nativo `<dialog>` resumiendo que el participante saldrá de la lista y se retirarán sus votos, exigiendo confirmación explícita con botón destructivo antes de procesar el abandono.

### Badge de Organizador en Modal de Creación (`_modal_crear.blade.php`)

* **Ergonomía H6 y H8:** Se elimina el input editable de organizador. En su lugar, se renderiza una etiqueta estática no editable:
  * *«Organizador: {{ auth()->user()->name }}»* estilizada con texto atenuado (`#6f6f6a`) e ícono de usuario.

### Tarjeta de Actividad (`ActivityCard`)

* **4 Dimensiones Autónomas del Evento (Reducción de Carga Cognitiva):**
  1. **Qué ocurrió:** Ícono semántico (creación, cambio de estado, membresía, propuesta, voto) y descripción de la acción.
  2. **Cuándo ocurrió:** Tiempo relativo en español (*"Hace 15 min"*) y fecha exacta legible (*"29 oct 2026, 10:30"*).
  3. **Resultado:** Badge semántico con el estado resultante (`pendiente`, `confirmado`, `cancelado`).
  4. **Elemento involucrado:** Nombre del plan con enlace directo a su sala (`/planes/{plan}`).
* **Forma y Contraste:** Tarjeta blanca con borde `#e3e3df`, radio de `12px` y padding `16px`.

### Modal de Desempate y Confirmación de Plan (`<dialog id="modal-confirmar-plan">`)

* **Propósito y Autorización:** Componente exclusivo del organizador (restringido por `@can('confirmar', $plan)`) para cerrar la deliberación y formalizar la fecha y el lugar definitivos del plan.
* **Geometría y Ergonomía:** Modal nativo `<dialog>`, ancho máximo de `520px` (`w-full max-w-[520px]`), fondo blanco `#ffffff`, bordes redondeados `16px`, padding `28px`, y backdrop oscuro `rgba(0, 0, 0, 0.4)`. Cierre por tecla `Escape` y botón visible «Cancelar» con separación antagónica ≥ 12 px (`gap-3`) y altura mínima de **44px** (Ley de Fitts).
* **Divulgación Progresiva y Ley de Hick:** Con $n \le 4$, se estructura la confirmación según el escenario:
  * **Ganador Indiscutido:** Si una opción de fecha o lugar tiene la mayor cantidad de votos sin empates, se muestra como veredicto preseleccionado claro, eliminando fricción cognitiva.
  * **Resolución de Empate:** Si existe empate en el primer lugar de votos entre 2 o más opciones, se despliega un selector delimitado exclusivamente al subconjunto de opciones empatadas (evitando listar opciones descartadas con menos votos).
* **Motivo Obligatorio en Empate:** Cuando hay empate en fecha o lugar, se exige un campo `<textarea name="motivo_desempate" required>` con longitud mínima de 5 caracteres para documentar la justificación pública de la decisión del organizador ante el grupo.

### Banner de Veredicto Público en Planes Confirmados

* **Propósito y Accesibilidad (Nielsen H1 y H5):** Notifica de forma inmediata y visible a todos los participantes e invitados el resultado final de la votación, evitando incertidumbre o intentos de seguir votando.
* **Tokens Visuales:** Contenedor destacado en color de éxito cálido (fondo `#edf7ef`, borde `#c2e2c8`, texto `#1c1c1a`, ícono de verificación `#25663a`).
* **Elementos de Veredicto:**
  1. Opción de Fecha acordada con ícono de calendario.
  2. Opción de Lugar o Destino acordado con ícono de ubicación.
  3. Bloque de Justificación del Organizador: si hubo resolución de desempate, se despliega una cita destacada: *«Decisión del organizador: [Motivo registrado]»*.
* **Inmutabilidad Visual:** En sala confirmada, los botones de votación se inhabilitan (`disabled` con texto explicativo) y el botón de sugerir nuevas opciones se oculta para todos los usuarios.

### Arquitectura de la Información y Listado de Planes (`listar.blade.php` y `_tabla_seccion.blade.php`)

* **Segmentación por Rol y Propósito (Hick & LATCH):**
  * La vista principal descompone los planes del usuario en 3 pestañas principales de navegación accesibles (`role="tab"` / `role="tablist"`), eliminando la sobrecarga cognitiva de mezclar responsabilidades dispares en una sola grilla:
    1. **«Mis planes creados»:** Enfoque en administración, deliberación, invitaciones y desempate.
    2. **«Planes donde participo»:** Enfoque en participación activa, votación y abandono preventivo.
    3. **«Invitaciones pendientes»:** Enfoque en triaje rápido con botones explícitos de «Aceptar» o «Rechazar».
  * Cada pestaña incorpora un badge numérico dinámico para anticipar el volumen de elementos sin obligar a cambiar de vista.
* **Organización Cronológica y de Urgencia en Secciones Modulares (`_tabla_seccion.blade.php`):**
  * Dentro de cada pestaña, los planes se ordenan jerárquicamente en 3 bloques temporales de decisión:
    1. **«Activos / en votación»:** Máxima urgencia visual. Planes en estado `pendiente` con deliberación abierta.
    2. **«Confirmados»:** Urgencia media/resolutiva. Planes con fecha y lugar definitivos acordados.
    3. **«Cancelados o pasados»:** Baja urgencia. Histórico inmutable para consulta o depuración definitiva.
* **Grilla Tabular Recta y Alineación de Acciones:**
  * Cabeceras de tabla con anchos fijos y previsibles (`Nombre del Plan` 28%, `Organizador/Estado` 18%, `Fecha y Hora` 20%, `Lugar` 14%, `Acciones` 20%).
  * Altura estándar de fila de **68px** para mantener ritmo visual continuo.
  * Grupo de acciones en flex alineado al final con separación de `8px` (`gap-2`) y botones con área mínima de contacto de **44×44px** (Fitts).
* **Estados Vacíos Empáticos y Orientados a la Acción:**
  * Si una sección o pestaña no tiene elementos, no se oculta silenciosamente ni muestra una tabla vacía: despliega una tarjeta de estado vacío con ícono suave, mensaje contextual en lenguaje natural y botón primario guiado (ej. «Crear mi primer plan»).
* **Acceso Ergonómico a la Sala en Listado:**
  * Enlace accesible en el nombre del plan con altura mínima táctil de 44px.
  * Botón compacto de acceso a sala con ícono nítido (`44×44px`, `border-radius: 50px`) en la celda de acciones para el organizador, y botón claro «Ver sala» para participantes.

### Avatar Acotado de Cabecera (`_cabecera.blade.php`)

* **Proporcionalidad Visual y Carga Cognitiva:** El avatar del organizador en la cabecera se mantiene en dimensiones estándar y acotadas (≤ 48 × 48 px, óptimo 36px–40px) alineado horizontalmente con su nombre y el chip de estado, evitando saturar la jerarquía visual del título del plan.

### Barra de Filtros Temáticos de Historial (`planes/historial.blade.php`)

* **Ley de Hick:** Conjunto acotado a $n = 4$ chips semánticos mutuamente excluyentes: `Todos`, `Estados`, `Creaciones` y `Participación`.
* **Ley de Fitts:** Cada chip garantiza un área táctil mínima de **44×44px** (`min-h-[44px]`). Chip activo resaltado con fondo amarillo cálido `#ffe683`.
* **Estado Vacío Empático:** Si el usuario no tiene actividad registrada, se muestra una ilustración/ícono con mensaje orientador y un botón de acción primaria «Explorar o crear un plan».

### Componente de Íconos SVG Vectoriales (`<x-icono nombre="..." />`)

* **Sustitución Radical de Emojis:** Resuelve la inconsistencia de diseño y fallos de lectura en tecnologías asistivas (Nielsen H2, H4 y H8).
* **Herencia Cromática Automática:** Construido con `stroke="currentColor"` y `fill="none"`, adaptándose directamente a las clases de texto de Tailwind y la paleta de Planazo (`text-brand-bar`, `text-muted`, `text-destructive`, `text-success`).
* **Dimensiones Normalizadas:**
  * Ícono compacto en botones pequeños: `16×16px` (`w-4 h-4`).
  * Ícono estándar en botones de acción y pestañas: `20×20px` (`w-5 h-5`).
  * Ícono destacado en tarjetas o estados vacíos: `24×24px` o `32×32px`.
* **Accesibilidad (WCAG 1.1.1):** Todo ícono decorativo incluye `aria-hidden="true"`. Si el botón solo contiene un ícono, incluye un span con clase `.sr-only` y atributo `aria-label` en el elemento interactivo.

### Panel de Gastos y Liquidación (`_gastos_liquidacion.blade.php`)

* **Pestaña Semántica en Sala (`role="tab"`):** Se integra en `detalle.blade.php` junto a *Votación* y *Participantes* para divulgación progresiva, evitando amontonar información heterogénea.
* **Tarjetas Autónomas de Balances Netos (Reducción de Carga Cognitiva):**
  * Bloque «Debes a compañeros»: Fondo suave `#fbeceb`, borde `#f5c6cb`, texto `#c0392b`, con lista de acreedores y botón directo de acción «Registrar pago».
  * Bloque «Te deben a ti»: Fondo suave `#edf7ef`, borde `#c2e2c8`, texto `#25663a`, con lista de deudores y estado de cobranza.
* **Modal de Registro de Gastos con Selector de Modo (Hick):**
  * Selector acotado a 2 modos: «En partes iguales» vs «Montos personalizados».
  * Si se selecciona personalizado, se despliegan dinámicamente los campos por participante, validando en tiempo real que la sumatoria coincida exactamente con el total del gasto.
* **Control de Liquidación de Pagos (Fitts y Freno de Emergencia):**
  * Pagos recibidos pendientes muestran dos botones antagónicos separados por `12px` (`gap-3`) con altura ≥ 44 px:
    * Botón primario de éxito «Confirmar pago» (verde cálido `#25663a`).
    * Botón secundario destructivo «Rechazar» que despliega modal `<dialog>` con textarea obligatorio para el motivo de rechazo.

