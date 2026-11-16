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
     - Campo de formulario:              rg -A 12 "^### D\. Campo de Formulario" resources/DESIGN.md
     - Componentes de contención/modal:  rg -A 30 "^## 4" resources/DESIGN.md
     - Feedback y prevención de errores: rg -A 35 "^## 5" resources/DESIGN.md
     - Telemetría y embudos (Dev):       rg -A 25 "^## 6" resources/DESIGN.md
     - Componentes de Salida Honesta:    rg -A 40 "^## 7" resources/DESIGN.md
     - Variantes por estado de dato:    rg -A 100 "^## 8" resources/DESIGN.md
     - Tarjeta / Avatar / Métrica:      rg -A 12 "^### [BDE]\. (Tarjeta|Avatar|Métrica)" resources/DESIGN.md
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

### H. Dimensiones de Avatar

| Token         | Valor  | Clase                   | Contexto de Uso                           |
| :------------ | :----- | :---------------------- | :---------------------------------------- |
| `--avatar-sm` | `28px` | `.avatar`               | Navbar (`.nav-usuario`), cabecera de sala |
| `--avatar-md` | `36px` | `.participantes-avatar` | Lista de participantes (sobre `.avatar`)  |
| `--avatar-lg` | `56px` | `.avatar-lg`            | Tarjeta de identidad en Mi Cuenta         |

### I. Tokens de Layout, Media y Tipografía

Declarados en `tokens.css` (secciones I-M). Una dimensión reutilizable **siempre** es un token; no se escribe `px` crudo en `app.css`.

| Token                                                         | Valor               | Uso                                                                   |
| :------------------------------------------------------------ | :------------------ | :-------------------------------------------------------------------- |
| `--icono-2xs` / `--icono-xs` / `--icono-btn`                  | `12/14/18px`        | Íconos de micro-badge, compactos y dentro de botones (§3.B)           |
| `--container-page` / `--container-md`                         | `1024px` / `920px`  | Ancho de página / de Mi Cuenta                                        |
| `--content-narrow`                                            | `400px`             | Toasts, tarjeta de acceso, tarjeta de edición, textos de estado vacío |
| `--dialog-sm` / `--dialog-md` / `--dialog-lg`                 | `400/480/520px`     | Anchos de `<dialog>` (§4.A)                                           |
| `--card-min-sm` / `--card-min-md`                             | `240px` / `300px`   | Mínimo de columna en grillas de tarjetas (`auto-fit`/`auto-fill`)     |
| `--nav-height`                                                | `60px`              | Alto de la barra superior (§3.C)                                      |
| `--thumb-sm` / `--thumb-md`                                   | `72px` / `128px`    | Miniaturas de QR e ilustraciones                                      |
| `--preview-sm` / `--preview-lg`                               | `160px` / `350px`   | Alto máximo de vistas previas de comprobante                          |
| `--line-height` / `--line-height-snug` / `--line-height-none` | `1.5` / `1.4` / `1` | Interlineado de lectura, compacto y de valores grandes                |
| `--letter-spacing-wide`                                       | `0.5px`             | Etiquetas en mayúsculas                                               |

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

### C. Barra de Navegación Superior (`.barra`)

Parcial `resources/views/layouts/_partials/nav.blade.php`. Sticky (`--z-nav`), alto `60px`, fondo `brand` (`#ffe683`), borde `border` y sombra `--shadow-sm`. Sin estilos inline: todo vive en `app.css`.

| Clase              | Función                                                            |
| :----------------- | :----------------------------------------------------------------- |
| `.barra`           | Contenedor flex; `justify-content: space-between`                  |
| `.barra-izquierda` | Agrupa marca + menú (gap `24px`); en móvil usa `display: contents` |
| `.marca`           | Logotipo textual (`font-size-2xl`, bold)                           |
| `.nav-menu`        | Contenedor del menú (`role="menubar"`) con items `.nav-item-*`     |
| `.barra-derecha`   | Usuario (`.nav-usuario` con `.avatar`) + botón `.boton-salir`      |
| `.form-reset`      | Formulario de cierre de sesión sin margen (clase global, §3.D)     |

**Responsive (breakpoint `720px`, no tokenizable porque `var()` no aplica en `@media`):**

| Elemento       | Escritorio                | Móvil (≤ 720px)                                                   |
| :------------- | :------------------------ | :---------------------------------------------------------------- |
| `.barra`       | 1 fila, alto `60px`       | 2 filas (`flex-wrap`), alto automático, padding `8px 16px`        |
| `.nav-menu`    | En línea junto a la marca | Segunda fila a ancho completo, scroll horizontal sin scrollbar    |
| `.nav-usuario` | Avatar + nombre           | Solo avatar (`.nav-usuario-nombre` oculto)                        |
| `.boton-salir` | Ícono + «Salir»           | Solo ícono (`aria-label="Cerrar sesión"`), objetivo táctil ≥ 44px |

### D. Campo de Formulario (`.campo` dentro de `.formulario`) — Alcance: global

Único campo de formulario del sistema (acceso, planes, Mi Cuenta, modales). Se envuelve el control y su etiqueta en `.campo`; no se estiliza el control con clases propias ni `style=""`.

- **`.formulario`:** columna con gap `--space-4` (los `.campo` dentro no llevan margen inferior).
- **Control (`input`, `select`, `textarea` dentro de `.campo`):** ancho 100%, `min-height: --touch-min` (Fitts), padding `--space-2 --space-3`, `font-size-base`, fondo `surface`, borde `border-input`, radio `--radius-md`. Checkbox, radio y hidden quedan fuera.
- **Etiqueta:** `font-size-sm`, `font-weight-semibold`, `foreground`.
- **Estados (Figma: propiedad `Estado`):** `Reposo`; `Foco` (outline 2px `foreground`, offset 2px, sin sombra); `Error` (clase `.invalido` en el control → borde `destructive`, mensaje `.campo-error` en `font-size-xs` `destructive`); `Placeholder` (`placeholder`). Texto de apoyo opcional `.campo-ayuda` (`font-size-xs`, `muted`).
- **Variante archivo (`input[type='file']`):** botón nativo con `::file-selector-button` (`surface-soft`, borde `border`, radio `--radius-md`; hover borde `primary`).
- **`.form-reset` / `.form-reset-inline`:** quitan el margen de un `<form>` que envuelve solo botones (no se crean clases `*-form` por pantalla).
- **Excepción documentada:** la barra de búsqueda/filtros (`.filtros`) mantiene forma de píldora (`--radius-pill`) por ser un buscador, no un campo de formulario.

---

## 4. Componentes de Contención y Diálogo

### A. Diálogos Modales Nativos (`<dialog>`) — Alcance: global

- HTML5 `<dialog>` nativo accesible (apertura con `.showModal()`, cierre con `.close()` o tecla `Escape`).
- **Forma única** definida en el selector `dialog` de `app.css`: fondo `surface`, radio `--radius-xl`, padding `--space-6`, sombra `--shadow-lg`, backdrop `--color-overlay`, alto máximo `100dvh − 2 × --space-4` con scroll interno y `overscroll-behavior: contain`; el fondo no hace scroll mientras hay un `<dialog open>`.
- **Tamaños (Figma: propiedad `Tamaño`):** `Small` (por defecto, `--dialog-sm` 400px), `Medium` (`.dialogo-md`, `--dialog-md` 480px), `Large` (`.dialogo-lg`, `--dialog-lg` 520px). Siempre limitados a `100vw − 2 × --space-4`.
- Cabecera con título H2 (`.dialogo-titulo`) e ícono, cuerpo con `.dialogo-mensaje` y pie de acciones `.dialogo-botones` / `.dialogo-botones-derecha` con separación `--space-3`.
- **Prohibido** crear clases de forma de diálogo por pantalla (`*-dialogo`); solo se elige el tamaño.

### B. Tarjeta (`.tarjeta`) — Alcance: global

Contenedor base de contenido. `.activity-card` comparte su declaración (misma superficie, borde, radio y sombra).

- **Base:** fondo `surface`, borde `1px solid border`, radio `--radius-xl` (`16px`), padding `--space-5` (`20px`), sombra `--shadow-sm`. Sin animaciones.
- **Partes opcionales:**

| Clase                   | Función                                                                          |
| :---------------------- | :------------------------------------------------------------------------------- |
| `.tarjeta-header`       | Fila con título + acción/badge, separada por borde inferior `border`             |
| `.tarjeta-titulo-grupo` | Agrupa ícono + `.tarjeta-titulo` dentro del header                               |
| `.tarjeta-titulo`       | `font-size-lg`, `font-weight-semibold`, `foreground`                             |
| `.tarjeta-desc`         | `font-size-sm`, `muted`                                                          |
| `.tarjeta-acciones`     | Fila de botones (gap `--space-3`, `flex-wrap`); cada botón respeta `--touch-min` |
| `.tarjeta-acciones-fin` | Modificador: alinea la botonera a la derecha                                     |

- **Variante `Peligro` (`.tarjeta-peligro`):** borde y separador `destructive-border`, título `destructive`. Para zonas de acción irreversible.
- **Figma:** componente `Tarjeta` con propiedad `Variante = Base | Peligro` y slots `Header`, `Descripción`, `Contenido`.

### C. Tablas Modulares de Datos (`.tabla-contenedor`)

- Contenedor con scroll horizontal, fondo `surface`, bordes redondeados y altura estándar de fila de `68px`.

### D. Avatar (`.avatar`) — Alcance: global

Círculo con la inicial del nombre. Fondo `foreground`, texto `secondary`, `font-weight-bold`, `--radius-circle`. Siempre decorativo (`aria-hidden="true"`); el nombre va en texto visible.

| Variante (Figma) | Clase                | Tamaño        | Fuente de texto |
| :--------------- | :------------------- | :------------ | :-------------- |
| `Small`          | `.avatar`            | `--avatar-sm` | `font-size-xs`  |
| `Large`          | `.avatar .avatar-lg` | `--avatar-lg` | `font-size-xl`  |

### E. Métrica (`.metrica`) — Alcance: global

Dato numérico con etiqueta, para resúmenes. Contenedor `.metricas-grid` (`repeat(auto-fit, minmax(110px, 1fr))`, gap `--space-3`).

- **Base (`.metrica`):** fondo `surface-soft`, borde `border`, radio `--radius-lg`, padding `--space-4`. Valor `.metrica-valor` en `font-size-2xl` bold; etiqueta `.metrica-label` en `font-size-xs` `muted`, sin uppercase.
- **Variante `Enlace` (`.metrica-enlace`):** `<a>` navegable. Reposo (`surface-soft`), hover (borde `primary`, fondo `surface`), focus-visible (outline 2px `primary`), alto mínimo `--touch-min`.
- **Figma:** componente `Métrica` con propiedad `Interactiva = false | true`.

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

### C. Selector de Exportación de Datos

Sin componente propio: se compone con `.tarjeta` (§4.B) + `.tarjeta-header` + `.tarjeta-desc` + `.tarjeta-acciones` con dos `.boton-claro` (`min-height` `--touch-min`, ícono `<x-icono nombre="descarga" />`).

### D. Layout de la Página Mi Cuenta — Alcance: solo `perfil/cuenta.blade.php`

Las clases con prefijo `.cuenta-*` pertenecen **únicamente** a esta página y solo resuelven su maquetación. El contenido visual reutiliza componentes globales (`.tarjeta`, `.avatar`, `.metrica`, `.badge-estado`).

| Clase                                                        | Función                                                                                                                           |
| :----------------------------------------------------------- | :-------------------------------------------------------------------------------------------------------------------------------- |
| `.cuenta-contenedor`                                         | Centrado, max-width `920px`, padding `--space-6 --space-4`                                                                        |
| `.cuenta-perfil` (+ `-datos`, `-nombre`, `-correo`, `-meta`) | Layout flex de la tarjeta de identidad (`.tarjeta` + `.avatar-lg`); elipsis con `min-width: 0`; bajo `480px` la meta baja de fila |
| `.cuenta-columnas` / `.cuenta-columna`                       | 2 columnas `minmax(0, 1fr)`, gap `--space-6`, `align-items: start`; 1 columna bajo `760px`                                        |
| `.cuenta-seccion-completa`                                   | Tarjeta a ancho completo bajo las columnas                                                                                        |
| `.cuenta-qr-*`                                               | Previsualización del QR de cobro (borde `border`, radio `--radius-md`)                                                            |
| `.cuenta-danger-*`                                           | Resumen dentro de `.tarjeta-peligro`                                                                                              |

> Los formularios de esta página usan el campo global `.campo` / `.formulario` (§3.D) y `.tarjeta-acciones .tarjeta-acciones-fin` (§4.B) para la botonera alineada a la derecha.

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

### D. Banner (`.banner`) — Alcance: global

Aviso contextual en bloque. Base `.banner`: padding `--space-4`, borde 1px, radio `--radius-lg`, `font-size-sm`. Partes: `.banner-icono`, `.banner-titulo` (md, bold), `.banner-texto`, `.banner-acciones`. Modificadores de layout: `.banner-fila` (ícono/contenido en fila), `.banner-espaciado` (contenido y acciones separados, con wrap).

| Variante (Figma) | Clase                 | Fondo            | Borde                | Texto         | Uso en Planazo                                      |
| :--------------- | :-------------------- | :--------------- | :------------------- | :------------ | :-------------------------------------------------- |
| `Neutro`         | `.banner-neutro`      | `neutral-bg`     | `border`             | `muted`       | Plan cancelado (solo lectura)                       |
| `Advertencia`    | `.banner-advertencia` | `warning-bg`     | `warning-border`     | `warning`     | Invitación pendiente; anulación automática de votos |
| `Peligro`        | `.banner-peligro`     | `destructive-bg` | `destructive-border` | `destructive` | Bloqueo por deuda pendiente (retiro del plan)       |

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

### F. Badge de Estado (`.badge-estado`) — Alcance: global

Píldora con borde (`--radius-pill`, `font-size-xs`, bold, ícono opcional `sm`, `white-space: nowrap`). Usada por `<x-activity-card />` y por «Métodos de cobro» en Mi Cuenta.

| Variante (Figma) | Clase                      | Fondo            | Borde                | Texto         | Uso                                    |
| :--------------- | :------------------------- | :--------------- | :------------------- | :------------ | :------------------------------------- |
| `Confirmado`     | `.badge-estado-confirmado` | `success-bg`     | `success-border`     | `success`     | Plan confirmado; cobro «Configurado»   |
| `Pendiente`      | `.badge-estado-pendiente`  | `warning-bg`     | `warning-border`     | `warning`     | Plan pendiente; cobro «Sin configurar» |
| `Cancelado`      | `.badge-estado-cancelado`  | `destructive-bg` | `destructive-border` | `destructive` | Plan cancelado                         |
| `Default`        | `.badge-estado-default`    | `neutral-bg`     | `border`             | `muted`       | Sin estado conocido                    |

---

### G. Diálogos de Eliminación de Cuenta por Condición (Divulgación Progresiva)

**Contención del diálogo (`dialog.dialogo-md`)**: forma y tamaño del diálogo global (§4.A); la lista de bloqueos y la botonera se resuelven con las clases de abajo.

**Lista de bloqueos (`.caja-salida-honesta-planes`)**: alto máximo `min(40dvh, 320px)` con scroll interno, de modo que título y botonera siempre quedan visibles aunque haya decenas de planes. Cada `.caja-salida-honesta-fila` es `flex` con gap `12px`:

| Elemento                          | Regla                                                                                |
| :-------------------------------- | :----------------------------------------------------------------------------------- |
| `.caja-salida-honesta-info`       | Columna (`flex: 1`, `min-width: 0`) con nombre + metadato                            |
| `.caja-salida-honesta-nombre`     | `font-weight-semibold`, `overflow-wrap: anywhere`, máximo 2 líneas con elipsis       |
| `.caja-salida-honesta-meta`       | `font-size-xs`, color `muted` («Plan abierto · Pendiente», «Deuda pendiente · X Bs») |
| Botón de resolución (`.boton-xs`) | `flex-shrink: 0` para no comprimirse con nombres largos                              |

**Botonera (`.dialogo-botones-derecha`)**: fila alineada a la derecha en escritorio; bajo `480px` se apila en columna con botones a `100%` de ancho e igual tamaño (simetría de Fitts).

| Condición de Usuario | Elemento Disparado       | Contenido del Modal                                                                                                                                                                         | Botones                                          |
| :------------------- | :----------------------- | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | :----------------------------------------------- |
| **Con Bloqueos**     | `#modal-bloqueos-cuenta` | Título «No puedes dar de baja tu cuenta todavía», una línea de contexto y filas `.caja-salida-honesta-fila` (plan/deuda + botón `.boton-xs` Gestionar/Liquidar). Sin prosa explicativa (H8) | `.boton-claro` "Entendido"                       |
| **Sin Bloqueos**     | `#modal-eliminar-cuenta` | Formulario con input contraseña actual (`#input-current-password`)                                                                                                                          | Botonera simétrica Fitts (Conservar vs Eliminar) |

---

### H. Tabla de Componentes (Referencia Rápida)

> Cada componente tiene un único origen. **Alcance** indica a quién pertenece: `Global` (reutilizable en cualquier vista) o la pantalla/flujo dueño.

| Componente                                                            | Alcance                      | Archivo de Referencia                               | Sección     |
| :-------------------------------------------------------------------- | :--------------------------- | :-------------------------------------------------- | :---------- |
| `.boton` / `.boton-claro` / `.boton-rojo`                             | Global                       | `resources/css/app.css`                             | §3.A        |
| `<x-icono nombre="..." />`                                            | Global                       | `components/icono.blade.php`                        | §3.B        |
| `.barra` / `.nav-menu` / `.nav-usuario`                               | Layout (`layouts/app`)       | `layouts/_partials/nav.blade.php` & `app.css`       | §3.C        |
| `<dialog>` (modal genérico)                                           | Global                       | `components/dialog-confirm.blade.php`               | §4.A        |
| `.tarjeta` (+ `-header`, `-titulo`, `-desc`, `-acciones`, `-peligro`) | Global                       | `resources/css/app.css`                             | §4.B        |
| `.formulario` / `.campo`                                              | Global                       | `resources/css/app.css`                             | §3.D        |
| `.tabla-contenedor`                                                   | Global                       | `resources/css/app.css`                             | §4.C        |
| `.avatar` / `.avatar-lg`                                              | Global                       | `resources/css/app.css`                             | §4.D        |
| `.metrica` / `.metrica-enlace`                                        | Global                       | `resources/css/app.css`                             | §4.E        |
| `<x-toast-alerta />`                                                  | Global                       | `components/toast-alerta.blade.php`                 | §5.A        |
| `.estado .estado-{valor}`                                             | Global                       | `resources/css/app.css`                             | §2.A + §8.A |
| `.badge-estado .badge-estado-{valor}`                                 | Global                       | `resources/css/app.css`                             | §8.F        |
| Chip pago / fila pago                                                 | Plan (`_gastos_liquidacion`) | `planes/_gastos_liquidacion.blade.php`              | §8.B + §8.C |
| `<x-activity-card />`                                                 | Global                       | `components/activity-card.blade.php`                | §8.E        |
| `.caja-salida-honesta`                                                | Salida Honesta               | `resources/css/app.css`                             | §7.A        |
| `<x-modal-salida-honesta />`                                          | Salida Honesta               | `components/modal-salida-honesta.blade.php`         | §7.B        |
| `<dialog>` + `.dialogo-md` / `.dialogo-lg`                            | Global                       | `resources/css/app.css`                             | §4.A        |
| `.banner` (+ `-neutro`, `-advertencia`, `-peligro`)                   | Global                       | `resources/css/app.css`                             | §8.D        |
| `.form-reset`                                                         | Global                       | `resources/css/app.css`                             | §3.D        |
| `.cuenta-*` (layout)                                                  | Solo Mi Cuenta               | `perfil/cuenta.blade.php` & `resources/css/app.css` | §7.D        |

---

## 9. Clases con Alcance de Pantalla

Las vistas **no** llevan `style=""` estático (solo se admite un valor dinámico de Blade, p. ej. `width: {{ $pct }}%`); lo hace cumplir `tests/Unit/DesignSystemGuardTest.php`. Lo específico de una pantalla vive en `app.css` bajo un prefijo que declara su dueño. Antes de crear una clase nueva se reutiliza un componente global (§3-§4, §8).

| Prefijo            | Dueño (vista)                                | Contenido                                                |
| :----------------- | :------------------------------------------- | :------------------------------------------------------- |
| `.cuenta-*`        | `perfil/cuenta.blade.php`                    | Layout de Mi Cuenta (§7.D)                               |
| `.gastos-*`        | `planes/_gastos_liquidacion.blade.php`       | Resumen, deudas, pagos, diálogos y formularios de gastos |
| `.votacion-*`      | `planes/_sala_votacion.blade.php`, `detalle` | Veredicto, pestañas, opciones y diálogos de votación     |
| `.cabecera-*`      | `planes/_cabecera.blade.php`                 | Cabecera de sala, banners, diálogo de retiro             |
| `.participantes-*` | `planes/_participantes.blade.php`            | Grilla de participantes, roles, diálogo de invitación    |
| `.tabla-seccion-*` | `planes/_tabla_seccion.blade.php`            | Columnas, celdas y diálogo de la tabla de planes         |
| `.listado-*`       | `planes/listar.blade.php`                    | Pestañas, secciones cronológicas y contadores            |
| `.historial-*`     | `planes/historial.blade.php`                 | Cabecera, filtros y estado vacío                         |
| `.analitica-*`     | `analitica/index.blade.php` (solo Dev)       | Embudos, errores y tabla de eventos                      |
| `.activity-card-*` | `components/activity-card.blade.php`         | Partes internas de la tarjeta de actividad               |

**Dimensiones locales permitidas** (no reutilizables, viven dentro de la clase de su dueño): ancho fijo del botón de acción (`104px`), alto de fila de tabla (`68px`) y ancho mínimo de tabla (`880px`), anchos de columna (`70-210px`), mínimos de campos en línea (`150-260px`), `110px` de métricas, `320px` de alto máximo de lista, `340px` de columna de embudo y bordes/outlines de 1-3px. Cualquier otro `px` crudo debe ser un token (§1.I); `DesignSystemGuardTest` lo hace cumplir.

**Hooks sin estilo:** una clase en una vista sin regla CSS solo se admite si la consumen JS, Playwright o PHPUnit (`badge`, `badge-escaneo`, `badge-estado-pago`, `badge-ia-verificado`, `badge-votos`, `boton-retirarse`, `check-participante`, `contenedor-monto-personalizado`, `discrepante`, `input-monto-indiv`, `seccion-participantes`, `toast-mensaje`, `verificado`); la lista vive en el test guardián.
