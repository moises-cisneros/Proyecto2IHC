# DESIGN.md — Sistema de Diseño y Guía de Interfaz de Planazo

> **Fuente de verdad visual y ergonómica de Planazo.**  
> Los tokens de diseño viven en [`resources/css/tokens.css`](css/tokens.css) (`@theme static`) y son importados por [`resources/css/app.css`](css/app.css).  
> **Regla de oro:** No inventar clases ni colores ad-hoc; reutilizar exclusivamente los tokens semánticos y componentes aquí catalogados para garantizar consistencia con Figma y el código.

<!-- GUÍA DE CONSULTA RÁPIDA CON RIPGREP (rg) PARA AGENTES:
     Para auditar o consultar una sección específica sin cargar todo el archivo:
     - Tokens de color nucleares:        rg -A 20 "^### A\. Tokens Nucleares" resources/DESIGN.md
     - Variaciones y superficies (bg):   rg -A 15 "^### B\. Variaciones de Superficie" resources/DESIGN.md
     - Tokens de tipografía:             rg -A 20 "^### C\. Tipografía" resources/DESIGN.md
     - Tokens de espaciado:              rg -A 15 "^### D\. Espaciado" resources/DESIGN.md
     - Tokens de geometría/radios:       rg -A 15 "^### E\. Geometría" resources/DESIGN.md
     - Semántica de estados y roles:     rg -A 35 "^## 2" resources/DESIGN.md
     - Componentes de acción (botones):  rg -A 25 "^## 3" resources/DESIGN.md
     - Componentes de contención/modal:  rg -A 30 "^## 4" resources/DESIGN.md
     - Feedback y prevención de errores: rg -A 35 "^## 5" resources/DESIGN.md
     - Telemetría y embudos (Dev):       rg -A 25 "^## 6" resources/DESIGN.md
     - Componentes de Salida Honesta:    rg -A 40 "^## 7" resources/DESIGN.md
     - Variantes por estado de dato:    rg -A 100 "^## 8" resources/DESIGN.md
-->

---

## 1. Fundamentos y Tokens de Diseño

> **Fuente de código:** los tokens se declaran modularmente en [`resources/css/tokens.css`](css/tokens.css) en `@theme static` (secciones A–H) y son importados en [`resources/css/app.css`](css/app.css), compilado por Vite y cargado con `@vite('resources/css/app.css')` en `layouts/app` y `layouts/acceso`. Todo el proyecto (CSS de componentes, estilos inline de Blade y JS) los consume con `var(--token)`. En Figma, cada token es una _variable_ con el mismo nombre.
>
> **Principios:** (1) sin animaciones ni transiciones; (2) sin librerías de UI, solo CSS plano + tokens; (3) Tailwind se usa únicamente por su capa `theme`/`utilities` (sin preflight).
>
> **Regla:** Un color, tamaño o radio que no tenga un token aquí NO existe en Planazo.

La paleta se organiza bajo una arquitectura limpia de tokens: **Tokens Nucleares** (identidad, roles primarios y semántica de estado) y **Variaciones de Superficie y Borde** (fondos suaves y contornos para tarjetas, chips y alertas).

### A. Tokens Nucleares (Paleta Principal)

| Token Semántico | Hex       | Rol y Comportamiento en Planazo                                            |
| :-------------- | :-------- | :------------------------------------------------------------------------- |
| `primary`       | `#60605f` | Botón primario (`.boton`), controles principales y acentos neutros         |
| `primary-hover` | `#4a4a49` | Interacción hover del botón primario                                       |
| `secondary`     | `#ffffff` | Botón secundario (`.boton-claro`), tarjetas y superficies activas          |
| `brand`         | `#ffe683` | Amarillo de identidad Planazo (navbar superior, acento activo)             |
| `brand-hover`   | `#ffd93d` | Hover en elementos de marca interactivos                                   |
| `surface`       | `#ffffff` | Fondo de tarjetas, tablas, modales y formularios                           |
| `background`    | `#f6f6f4` | Fondo general de la aplicación (`body`)                                    |
| `foreground`    | `#1c1c1a` | Texto de lectura principal de alto contraste                               |
| `muted`         | `#6f6f6a` | Texto secundario, subtítulos, etiquetas y metadatos                        |
| `border`        | `#e3e3df` | Borde estructural estándar (tarjetas, inputs, separadores)                 |
| `border-input`  | `#d1d1cd` | Borde de campos de formulario interactivos                                 |
| `success`       | `#25663a` | Aprobación, confirmación, pagos validados y estado completado              |
| `destructive`   | `#c0392b` | Acciones irreversibles, errores, bloqueos y botón eliminar (`.boton-rojo`) |
| `warning`       | `#8a6100` | Avisos importantes, procesos pendientes y votaciones en curso              |

---

### B. Variaciones de Superficie, Bordes y Badges (Contextuales)

Fondos atenuados de bajo impacto visual diseñados para garantizar contraste WCAG con sus textos respectivos:

| Token de Variación    | Hex                   | Token Nuclear Asociado    | Uso en Interfaz                                      |
| :-------------------- | :-------------------- | :------------------------ | :--------------------------------------------------- |
| `success-bg`          | `#edf7ef`             | `success` (`#25663a`)     | Fondo de chips confirmados y alertas de éxito        |
| `success-border`      | `#c2e2c8`             | `success` (`#25663a`)     | Borde de tarjetas y badges de confirmación           |
| `destructive-bg`      | `#fbeceb`             | `destructive` (`#c0392b`) | Fondo de avisos de error y bloqueos de eliminación   |
| `destructive-border`  | `#f5c6cb`             | `destructive` (`#c0392b`) | Borde sutil de advertencias críticas                 |
| `warning-bg`          | `#fff4d6`             | `warning` (`#8a6100`)     | Fondo de estado pendiente y avisos de anulación      |
| `warning-border`      | `#ffe683`             | `brand` / `warning`       | Borde de banners informativos                        |
| `neutral-bg`          | `#eeeeec`             | `muted` (`#6f6f6a`)       | Fondo de chips cancelados y estados desactivados     |
| `surface-soft`        | `#fbfbf9`             | `surface` (`#ffffff`)     | Fondo de cabeceras de tabla y contenedores anidados  |
| `destructive-hover`   | `#a63125`             | `destructive` (`#c0392b`) | Hover del botón destructivo (`.boton-rojo`)          |
| `foreground-soft`     | `#2e2e2c`             | `foreground` (`#1c1c1a`)  | Hover de elementos oscuros (nav activo, toolbar dev) |
| `placeholder`         | `#b0b0aa`             | `muted` (`#6f6f6a`)       | Texto de placeholder en campos                       |
| `edit` / `edit-hover` | `#a3a60b` / `#8e9109` | —                         | Botón de edición (`.boton-editar`)                   |
| `overlay`             | `rgb(0 0 0 / 0.4)`    | —                         | Fondo de `dialog::backdrop`                          |

---

### C. Tipografía y Escala Jerárquica

Tokens definidos en `@theme static` de `resources/css/tokens.css`:

| Token                                 | Valor                                       | Uso                                               |
| :------------------------------------ | :------------------------------------------ | :------------------------------------------------ |
| `--font-family`                       | `system-ui, 'Segoe UI', Roboto, sans-serif` | Familia base (Instrument Sans como fallback Vite) |
| `--font-size-md`                      | `14px`                                      | Texto de componentes (botones, alertas, celdas)   |
| `--font-family` (mono: `--font-mono`) | —                                           | Etiquetas técnicas de telemetría                  |
| `--font-size-base`                    | `15px`                                      | Cuerpo de lectura principal                       |
| `--font-size-sm`                      | `13px`                                      | Metadatos, subtítulos, etiquetas                  |
| `--font-size-xs`                      | `12px`                                      | Microcopia, badges, chips                         |
| `--font-size-lg`                      | `18px`                                      | Títulos de sección, subtítulos de modal           |
| `--font-size-xl`                      | `22px`                                      | Títulos de tarjeta, encabezados                   |
| `--font-size-2xl`                     | `28px`                                      | H1, título principal de sala                      |
| `--line-height`                       | `1.5`                                       | Altura de línea base                              |
| `--font-weight-normal`                | `400`                                       | Texto regular                                     |
| `--font-weight-medium`                | `500`                                       | Labels, botones secundarios                       |
| `--font-weight-semibold`              | `600`                                       | Títulos, botones primarios                        |
| `--font-weight-bold`                  | `700`                                       | H1, montos destacados, badges                     |

---

### D. Espaciado

Escala de espaciado consistente para padding, gap y margin:

| Token        | Valor  | Uso típico                                         |
| :----------- | :----- | :------------------------------------------------- |
| `--space-1`  | `4px`  | Gap mínimo entre íconos y texto                    |
| `--space-2`  | `8px`  | Gap entre botones en fila                          |
| `--space-3`  | `12px` | Separación antagónica mínima (Fitts), gap estándar |
| `--space-4`  | `16px` | Padding interno de campos, margen entre secciones  |
| `--space-5`  | `20px` | Padding de modales, margen de banners              |
| `--space-6`  | `24px` | Padding de tarjetas y contenedores principales     |
| `--space-8`  | `32px` | Separación entre bloques mayores                   |
| `--space-10` | `40px` | Margen superior de contenido                       |

---

### E. Geometría y Formas

| Token             | Valor  | Uso                                                         |
| :---------------- | :----- | :---------------------------------------------------------- |
| `--radius-sm`     | `6px`  | Chips de texto, botones compactos                           |
| `--radius-md`     | `10px` | Campos de formulario (acceso)                               |
| `--radius-lg`     | `12px` | Tarjetas secundarias, tablas, banners                       |
| `--radius-xl`     | `16px` | Tarjetas principales, modales, cabecera                     |
| `--radius-pill`   | `50px` | Botones, chips de filtro, buscador, badges de estado        |
| `--radius-circle` | `50%`  | Avatares y puntos indicadores                               |
| `--touch-min`     | `44px` | Área táctil mínima Fitts (todos los controles interactivos) |

### F. Dimensiones de Íconos Vectoriales

Tokens declarados en `@theme static` de `tokens.css` para normalizar las escalas SVG:

| Token        | Valor  | Clase Asociada | Prop Componente | Contexto de Uso                                               |
| :----------- | :----- | :------------- | :-------------- | :------------------------------------------------------------ |
| `--icono-sm` | `16px` | `.icono-sm`    | `tamano="sm"`   | Íconos en navegación (`.nav-item-icono`), badges y microcopia |
| `--icono-md` | `20px` | `.icono-md`    | `tamano="md"`   | Tamaño estándar por defecto (`.icono-svg`), títulos y alertas |
| `--icono-lg` | `24px` | `.icono-lg`    | `tamano="lg"`   | Íconos destacados, banners de estado y cabeceras de sala      |

> En botones (`.boton`, `.boton-claro`, `.boton-rojo`, `.boton-simetrico`), los íconos se escalan automáticamente a `18px` para guardar balance con la tipografía.

### G. Elevación y Capas

| Token                                    | Valor                           | Uso                      |
| :--------------------------------------- | :------------------------------ | :----------------------- |
| `--shadow-sm`                            | `0 1px 3px rgb(0 0 0 / 0.05)`   | Tarjetas, tablas, navbar |
| `--shadow-md`                            | `0 4px 12px rgb(0 0 0 / 0.1)`   | Toasts, toolbar flotante |
| `--shadow-lg`                            | `0 10px 25px rgb(0 0 0 / 0.15)` | Diálogos modales         |
| `--z-nav` / `--z-floating` / `--z-toast` | `40` / `50` / `60`              | Orden de capas           |

- **Navbar:** Sticky superior (`top: 0; z-index: 40; height: 60px; background: brand; border-bottom: border`).

---

## 2. Semántica de Estados y Roles

### A. Chips de Estado de Recursos

| Estado       | Token Fondo                  | Token Borde                      | Token Texto               | Significado en Interacción                               |
| :----------- | :--------------------------- | :------------------------------- | :------------------------ | :------------------------------------------------------- |
| `pendiente`  | `warning-bg` (`#fff4d6`)     | `warning-border` (`#ffe683`)     | `warning` (`#8a6100`)     | Abierto a cambios/votación o en espera de resolución.    |
| `confirmado` | `success-bg` (`#edf7ef`)     | `success-border` (`#c2e2c8`)     | `success` (`#25663a`)     | Acordado o aprobado definitivamente. Inmutable.          |
| `cancelado`  | `neutral-bg` (`#eeeeec`)     | `border` (`#e3e3df`)             | `muted` (`#6f6f6a`)       | Detenido sin efecto posterior.                           |
| `bloqueado`  | `destructive-bg` (`#fbeceb`) | `destructive-border` (`#f5c6cb`) | `destructive` (`#c0392b`) | Detenido por regla de negocio (deuda o planes abiertos). |

### B. Badges de Rol y Autorización

| Rol                     | Presentación Visual                                                 | Capacidades Ergonómicas                                       |
| :---------------------- | :------------------------------------------------------------------ | :------------------------------------------------------------ |
| `Organizador / Creador` | Sólido (`background: primary; color: #ffffff`)                      | Gestión total, desempate y cierre. No puede huir del recurso. |
| `Participante Activo`   | Contorno (`background: surface; border: border; color: foreground`) | Proponer, votar, retirarse voluntariamente si no adeuda.      |
| `Invitado Pendiente`    | Atenuado (`background: neutral-bg; color: muted`)                   | Solo lectura hasta aceptar o declinar membresía.              |

---

## 3. Componentes de Acción e Interacción (Fitts)

### A. Botones Estándar (`.boton*`)

- **Regla de Fitts Inviolable:** Área táctil mínima de **44×44px** (`min-height: 44px`).
- **Separación Antagónica:** Separación mínima de **12px** (`gap: 12px` / `gap-3`) entre acciones contiguas.
- **Variantes Oficiales:**
    - **Primario (`.boton`):** Fondo `primary` (`#60605f`), texto blanco, radio `50px`, padding `9px 16px`.
    - **Secundario (`.boton-claro`):** Fondo `surface` (`#ffffff`), borde `border` (`#e3e3df`), texto `foreground`, radio `50px`.
    - **Destructivo (`.boton-rojo`):** Fondo `destructive` (`#c0392b`), texto blanco, radio `50px`.
    - **Procesando / Deshabilitado:** Opacidad `0.4`, cursor `not-allowed`.

### B. Componente de Íconos Vectoriales (`<x-icono nombre="..." />`)

- Prohibido el uso de emojis crudos en vistas. Usar trazados Heroicons/Lucide con `stroke="currentColor"`.
- Tamaños normados: Compacto `16×16px` (`tamano="sm"` / `.icono-sm`), Estándar `20×20px` (`tamano="md"` / `.icono-md` / por defecto), Destacado `24×24px` (`tamano="lg"` / `.icono-lg`).
- Proporción en botones: Todos los botones (`.boton`, `.boton-claro`, `.boton-rojo`, `.boton-simetrico`) acoplan sus íconos a `18×18px` de forma automática.
- En SVG nativo, el componente `<x-icono>` emite atributos intrínsecos `width` y `height` acordes a la escala para garantizar contención geométrica estricta frente a flexbox y prevenir desbordes.
- Accesibilidad: Siempre con texto visible o `aria-label` descriptivo.

---

## 4. Componentes de Contención y Diálogo

### A. Diálogos Modales Nativos (`<dialog>`)

- HTML5 `<dialog>` nativo accesible (apertura con `.showModal()`, cierre con `.close()` o tecla `Escape`).
- Ancho `400-520px`, fondo `surface` (`#ffffff`), radio `16px`, padding `24-28px`, backdrop `rgba(0,0,0,0.4)`.
- Cabecera con título H2 e ícono, cuerpo con advertencia contextual y pie de acciones alineado a la derecha con separación de `12px`.

### B. Tarjetas Autónomas (`.tarjeta` / `.card`)

- Fondo `surface` (`#ffffff`), borde `1px solid border` (`#e3e3df`), radio `12px`, padding `16-20px`.

### C. Tablas Modulares de Datos (`.tabla-contenedor`)

- Contenedor con scroll horizontal, fondo `surface`, bordes redondeados y altura estándar de fila de `68px`.

---

## 5. Componentes de Información, Feedback y Prevención

### A. Notificaciones Flotantes Nativas (`<x-toast-alerta />`)

- Posición fija `top: 20px; right: 20px; z-index: 50;`.
- Descarte automático a los 3 segundos (sin animación), botón de cierre manual $\ge 44$ px y `aria-live="polite"`.
- Variantes: Éxito (`success-bg` / `success`) y Error (`destructive-bg` / `destructive`).

### B. Banners de Advertencia y Freno de Emergencia (Nielsen H5 y H6)

- **Banner de Freno / Bloqueo:** Fondo `destructive-bg` (`#fbeceb`), borde `destructive-border` (`#f5c6cb`), texto `destructive` (`#c0392b`).
- **Banner Informativo:** Fondo `warning-bg` (`#fff4d6`), borde `warning-border` (`#ffe683`), texto `warning` (`#8a6100`).

---

## 6. Telemetría y Observabilidad de Interacción (Solo Dev)

### A. Dev Floating Toolbar (`dev_toolbar.blade.php`)

- Píldora discreta fija en `fixed bottom-4 right-4 z-50` (`[📊 Telemetría Dev · /analitica]`).
- Exclusiva para entornos `local` y `testing`. No contamina el menú del usuario común.

### B. Tarjetas de Embudo y Métricas (`.tarjeta-embudo`)

- Fondo `surface` (`#ffffff`), borde `border` (`#e3e3df`), radio `12px`.
- Avance visual mediante barra rellena con token oficial `brand` (`#ffe683`).
- Caja de finalización en `success-bg` (`#edf7ef`) con texto `success` (`#25663a`).

---

## 7. Componentes de Salida Honesta y Prevención de Patrones Oscuros

### A. Caja Informativa de Salida Honesta (`.caja-salida-honesta`)

Componente contenedor para explicar bloqueos de eliminación estructurado en dos partes: **Por qué** no se puede realizar la acción y **Qué hacer** para resolverla (sin callejones sin salida / _anti-roach motel_).

- **Estructura y Tokens Semánticos:**
    - **Fondo:** `destructive-bg` (`#fbeceb`).
    - **Borde:** `1px solid destructive-border` (`#f5c6cb`).
    - **Radio:** `12px` (`rounded-xl`).
    - **Padding:** `16px 18px`.
    - **Texto Explicativo:** `13px foreground` (`#1c1c1a`), interlineado 1.45.
    - **Acciones Sugeridas:** Enlaces y botones de resolución en `.boton-claro` con altura mínima $\ge 44$ px.

### B. Diálogo de Salida Simétrica (`<x-modal-salida-honesta />`)

Modal especializado derivado de `<dialog>` bajo estricta **Prueba de Simetría** (_"Salir tiene que ser tan fácil como entrar"_):

- **Geometría y Jerarquía Simétrica:**
    - Ancho máximo `480px`, fondo `surface` (`#ffffff`), radio `16px`, padding `28px`.
    - **Cero confirmshaming:** Lenguaje neutral y asertivo sin reproches ni emojis de culpa.
    - **Botonera de Acción Simétrica:**
        - Botón de Permanencia: `.boton-claro` (`surface` con borde `border`).
        - Botón de Salida Destructiva: `.boton-rojo` (fondo `destructive` `#c0392b`).
        - Idéntica altura táctil $\ge 44$ px, padding simétrico (`10px 22px`) y separación antagónica $\ge 12$ px.

### C. Selector de Exportación de Datos (`.tarjeta-exportacion-datos`)

Tarjeta para el ejercicio de derechos de portabilidad de información (acceso y descarga):

- **Tokens Semánticos:**
    - Fondo `surface` (`#ffffff`), borde `1px solid border` (`#e3e3df`), radio `12px`, padding `20px`.
    - Título H2: `18px font-semibold foreground` (`#1c1c1a`).
    - Texto descriptivo: `14px muted` (`#6f6f6a`).
    - Botones de descarga directa: `.boton-claro` con altura mínima de 44px e ícono vectorial `<x-icono nombre="descarga" />`.

---

## 8. Variantes de Componente por Estado de Dato

> **Propósito:** Documenta cómo cambia el aspecto visual de cada componente según el **estado del dato** (`pendiente`, `confirmado`, `cancelado`, `rechazado`, `bloqueado`), no los estados de interacción CSS. Cada fila es una variante nombrada en Figma y su token semántico de referencia para el código.

---

### A. Chip de Estado de Plan (`.estado .estado-{valor}`)

Definido en `resources/css/app.css` (sección Componentes). Clase base `.estado` + modificador semántico.

| Variante (Figma) | Clase CSS            | Fondo     | Texto     | Token Fondo  | Token Texto |
| :--------------- | :------------------- | :-------- | :-------- | :----------- | :---------- |
| `Pendiente`      | `.estado-pendiente`  | `#fff4d6` | `#8a6100` | `warning-bg` | `warning`   |
| `Confirmado`     | `.estado-confirmado` | `#edf7ef` | `#25663a` | `success-bg` | `success`   |
| `Cancelado`      | `.estado-cancelado`  | `#eeeeec` | `#6f6f6a` | `neutral-bg` | `muted`     |

> **Nota:** `.estado-bloqueado` y `.estado-rechazado` existen y usan `destructive-bg` / `destructive`.

---

### B. Chip de Estado de Pago (Inline en `_gastos_liquidacion.blade.php`)

Estos chips se renderizan con estilos inline por ahora. Los tokens semánticos de referencia son:

| Variante (Figma)  | Estado del Dato | Fondo del chip | Color del chip | Token Fondo      | Token Texto   |
| :---------------- | :-------------- | :------------- | :------------- | :--------------- | :------------ |
| `Pago Pendiente`  | `pendiente`     | `#fff4d6`      | `#8a6100`      | `warning-bg`     | `warning`     |
| `Pago Confirmado` | `confirmado`    | `#edf7ef`      | `#25663a`      | `success-bg`     | `success`     |
| `Pago Rechazado`  | `rechazado`     | `#fbeceb`      | `#c0392b`      | `destructive-bg` | `destructive` |

> **Estado:** migrado a tokens oficiales (deuda saldada). Los estilos inline usan `var(--color-*)`.

---

### C. Fila / Tarjeta de Pago (contenedor en `_gastos_liquidacion.blade.php` L186)

El contenedor de cada fila de pago cambia fondo y borde según el estado del pago:

| Variante (Figma)       | Estado del Dato | Fondo del contenedor | Borde     | Token Fondo      | Token Borde          |
| :--------------------- | :-------------- | :------------------- | :-------- | :--------------- | :------------------- |
| `Fila Pago Pendiente`  | `pendiente`     | `#fff4d6`            | `#ffe683` | `warning-bg`     | —                    |
| `Fila Pago Confirmado` | `confirmado`    | `#edf7ef`            | `#c2e2c8` | `success-bg`     | `success-border`     |
| `Fila Pago Rechazado`  | `rechazado`     | `#fbeceb`            | `#f5c6cb` | `destructive-bg` | `destructive-border` |

> **Estado:** migrado a tokens oficiales (deuda saldada). Los estilos inline usan `var(--color-*)`.

---

### D. Banner Contextual de Sala de Plan (`_cabecera.blade.php`)

La cabecera de la sala inyecta banners distintos según el estado del plan o la condición del participante:

| Variante (Figma)   | Condición                      | Fondo     | Borde     | Texto     | Token Fondo      | Token Borde          |
| :----------------- | :----------------------------- | :-------- | :-------- | :-------- | :--------------- | :------------------- |
| `Banner Cancelado` | `plan->estado === 'cancelado'` | `#eeeeec` | `#e3e3df` | `#6f6f6a` | `neutral-bg`     | `border`             |
| `Banner Invitado`  | Participante no aceptó aún     | `#fff4d6` | `#ffe683` | `#8a6100` | `warning-bg`     | `warning-border`     |
| `Banner Deuda`     | `usuarioTieneDeuda()`          | `#fbeceb` | `#f5c6cb` | `#c0392b` | `destructive-bg` | `destructive-border` |
| `Banner Info Pago` | Pago pendiente de votación     | `#fff4d6` | `#ffe683` | `#8a6100` | `warning-bg`     | `warning-border`     |

> **Estado:** migrado a tokens oficiales (deuda saldada). Los estilos inline usan `var(--color-*)`.

---

### E. Activity Card (`.activity-card` / `<x-activity-card />`)

Definido en `resources/views/components/activity-card.blade.php` L10–18. El tipo de evento determina el estilo del chip de la tarjeta de actividad:

| Variante (Figma)            | Tipo de Evento    | Fondo     | Borde     | Texto     | Token de Referencia                                        |
| :-------------------------- | :---------------- | :-------- | :-------- | :-------- | :--------------------------------------------------------- |
| `Actividad Confirmado`      | `confirmado`      | `#edf7ef` | `#c2e2c8` | `#25663a` | `success-bg` / `success-border` / `success` ✅             |
| `Actividad Cancelado`       | `cancelado`       | `#fbeceb` | `#f5c6cb` | `#c0392b` | `destructive-bg` / `destructive-border` / `destructive` ✅ |
| `Actividad Pendiente`       | `pendiente`       | `#fff4d6` | `#ffe683` | `#8a6100` | `warning-bg` / `warning-border` / `warning` ✅             |
| `Actividad Pago Confirmado` | `pago_confirmado` | `#edf7ef` | `#c2e2c8` | `#25663a` | `success-*` ✅                                             |
| `Actividad Pago Rechazado`  | `pago_rechazado`  | `#fbeceb` | `#f5c6cb` | `#c0392b` | `destructive-*` ✅                                         |

> **Estado:** migrado a tokens oficiales (deuda saldada). Los estilos inline usan `var(--color-*)`.

---

### F. Tabla de Componentes (Referencia Rápida)

> Esta tabla permite a Figma y al código mantenerse sincronizados: cada componente del sistema de diseño tiene un único archivo Blade o CSS de referencia.

| Componente                                | Archivo de Referencia                                                        | Sección DESIGN.md |
| :---------------------------------------- | :--------------------------------------------------------------------------- | :---------------- |
| `.boton` / `.boton-claro` / `.boton-rojo` | `resources/css/app.css`                                                      | §3.A              |
| `<x-icono nombre="..." />`                | `resources/views/components/icono.blade.php`                                 | §3.B              |
| `<dialog>` (modal genérico)               | `resources/views/components/dialog-confirm.blade.php`                        | §4.A              |
| `.tarjeta` / `.card`                      | Uso inline en vistas                                                         | §4.B              |
| `.tabla-contenedor`                       | Uso inline en vistas                                                         | §4.C              |
| `<x-toast-alerta />`                      | `resources/views/components/toast-alerta.blade.php`                          | §5.A              |
| `.estado .estado-{valor}`                 | `resources/css/app.css`                                                      | §2.A + §8.A       |
| Chip pago / fila pago                     | `planes/_gastos_liquidacion.blade.php` L186–194                              | §8.B + §8.C       |
| Banner sala contextual                    | `planes/_cabecera.blade.php` L58–163                                         | §8.D              |
| `<x-activity-card />`                     | `resources/views/components/activity-card.blade.php`                         | §8.E              |
| `.caja-salida-honesta`                    | Uso inline (a crear en T10)                                                  | §7.A              |
| `<x-modal-salida-honesta />`              | `resources/views/components/modal-salida-honesta.blade.php` (a crear en T10) | §7.B              |
| `.tarjeta-exportacion-datos`              | Vista `mi-cuenta` (a crear en T10)                                           | §7.C              |
