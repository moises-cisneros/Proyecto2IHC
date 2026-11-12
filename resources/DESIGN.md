# DESIGN.md — Sistema de Diseño y Guía de Interfaz de Planazo

> **Fuente de verdad visual y ergonómica de Planazo.**  
> Abstrae los tokens y clases reales de [`public/css/app.css`](../public/css/app.css) y [`resources/css/app.css`](css/app.css).  
> **Regla de oro:** No inventar clases ni estilos ad-hoc; reutilizar exclusivamente los componentes y tokens agnósticos aquí catalogados.

<!-- GUÍA DE CONSULTA RÁPIDA CON RIPGREP (rg) PARA AGENTES:
     Para auditar o consultar una sección específica sin cargar todo el archivo:
     - Fundamentos y tokens de color:    rg -A 30 "^## 1" resources/DESIGN.md
     - Semántica de estados y roles:     rg -A 35 "^## 2" resources/DESIGN.md
     - Componentes de acción (botones):  rg -A 25 "^## 3" resources/DESIGN.md
     - Componentes de contención/modal:  rg -A 30 "^## 4" resources/DESIGN.md
     - Componentes de feedback/info:     rg -A 35 "^## 5" resources/DESIGN.md
     - Telemetría y embudos (Dev):       rg -A 25 "^## 6" resources/DESIGN.md
-->

---

## 1. Fundamentos y Tokens de Diseño

### A. Paleta de Colores Semántica

| Token Semántico    | Hex / RGB                      | Uso Específico en Planazo                                   |
| :----------------- | :----------------------------- | :---------------------------------------------------------- |
| `background`       | `#f6f6f4`                      | Fondo general de la página (`body`)                         |
| `surface` / `card` | `#ffffff`                      | Fondo de tarjetas, tablas, modales y formularios            |
| `foreground`       | `#1c1c1a`                      | Texto principal de alta legibilidad                         |
| `muted`            | `#6f6f6a`                      | Texto de apoyo, encabezados y metadatos secundarios         |
| `border`           | `#e3e3df`                      | Bordes de inputs, tablas, separadores y tarjetas            |
| `brand-bar`        | `#ffe683`                      | Amarillo cálido del navbar y chips activos                  |
| `brand-hover`      | `#ffd93d`                      | Estado hover de la barra y chips interactivos               |
| `primary`          | `#60605f`                      | Botón primario (`.boton`), píldora con texto `#ffffff`      |
| `primary-hover`    | `#4a4a49`                      | Hover del botón primario                                    |
| `secondary`        | `#ffffff`                      | Botón secundario (`.boton-claro`) con borde `#e3e3df`       |
| `destructive`      | `#c0392b` / `rgb(185, 14, 19)` | Botón eliminar (`.boton-rojo`), errores y alertas           |
| `success`          | `#25663a`                      | Texto de confirmación, estado confirmado y alertas de éxito |
| `success-bg`       | `#edf7ef`                      | Fondo de alerta y chip confirmado                           |
| `pending`          | `#8a6100`                      | Texto de estado pendiente                                   |
| `pending-bg`       | `#fff4d6`                      | Fondo de chip de estado pendiente                           |
| `canceled`         | `#6f6f6a`                      | Texto de estado cancelado                                   |
| `canceled-bg`      | `#eeeeec`                      | Fondo de chip de estado cancelado                           |

### B. Tipografía y Escala

- **Fuente Base:** `system-ui, "Segoe UI", Roboto, sans-serif` (respaldada por `'Instrument Sans'`).
- **Jerarquía:** H1: `24-28px` bold (`#1c1c1a`); H2: `18-20px` semibold; Cuerpo: `15px` line-height 1.5; Metadatos: `13px` (`#6f6f6a`).

### C. Formas y Geometría Espacial

- **Estilo Píldora (`rounded-full` / `50px`):** Botones, badges, chips de filtro, inputs de búsqueda y nav-links.
- **Estilo Tarjeta (`rounded-xl` / `12-16px`):** Modales `<dialog>`, tarjetas autónomas y tablas.
- **Contenedor Maestro:** Ancho máximo `max-w-[1024px]` centrado con padding simétrico `24px 16px`.
- **Navbar Fijo:** `position: sticky; top: 0; z-index: 40;`, altura `60px`, logo `24px`.

---

## 2. Semántica de Estados y Roles (Dominio Agnóstico)

### A. Chips de Estado de Recursos

| Estado                    | Presentación Visual                                      | Significado en Interacción                                       |
| :------------------------ | :------------------------------------------------------- | :--------------------------------------------------------------- |
| `pendiente`               | Fondo `#fff4d6`, texto `#8a6100`                         | Abierto a cambios/votación o pendiente de validación humana.     |
| `confirmado`              | Fondo `#edf7ef`, texto `#25663a`                         | Acordado o aprobado definitivamente. Inmutable: no reversible.   |
| `cancelado` / `rechazado` | Fondo `#eeeeec` (`#fbeceb`), texto `#6f6f6a` (`#c0392b`) | Detenido o desestimado con motivo. No altera balances ni saldos. |

### B. Badges de Rol y Autorización

| Rol                     | Presentación Visual                                     | Capacidades Ergonómicas                                              |
| :---------------------- | :------------------------------------------------------ | :------------------------------------------------------------------- |
| `Organizador / Creador` | Badge sólido (`bg-[#60605f] text-white`)                | Gestión total, desempate y cierre. No puede retirarse del recurso.   |
| `Participante Activo`   | Badge con contorno (`border border-[#e3e3df] bg-white`) | Proponer, votar, retirarse voluntariamente si no posee deudas netas. |
| `Invitado Pendiente`    | Badge atenuado (`bg-[#eeeeec] text-[#6f6f6a]`)          | Solo lectura hasta aceptar o declinar la membresía.                  |

---

## 3. Componentes de Acción e Interacción (Clic / Fitts)

### A. Botones Estándar (`.boton*`)

- **Regla de Fitts Inviolable:** Todo botón garantiza un área táctil mínima de **44×44px** (`min-height: 44px` o `py-2.5 px-4`).
- **Separación Antagónica:** Controles destructivos contiguos a constructivos mantienen separación mínima de **12px** (`gap-3`).
- **Variantes:**
    - **Primario (`.boton`):** Fondo `#60605f`, texto blanco, radio `50px`, padding `9px 16px`.
    - **Secundario (`.boton-claro`):** Fondo blanco, borde `#e3e3df`, texto `#1c1c1a`, radio `50px`.
    - **Destructivo (`.boton-rojo`):** Fondo `#c0392b`, reservado para eliminaciones, rechazos o retiros.
    - **Estado Procesando (`.boton-procesando` / `:disabled`):** Opacidad `0.4`, cursor `not-allowed`, spinner SVG.

### B. Componente de Íconos Vectoriales (`<x-icono nombre="..." />`)

- **Directiva:** Prohibido el uso de emojis crudos en vistas. Usar trazados Heroicons/Lucide con `stroke="currentColor"`.
- **Escala de Tamaños:** Compacto: `16×16px` (`w-4 h-4`); Estándar: `20×20px` (`w-5 h-5`); Destacado: `24×24px`.
- **Accesibilidad:** Siempre acompaña texto descriptivo o atributo `aria-label` con `aria-hidden="true"` en el SVG.

### C. Píldoras de Navegación (`<x-nav-link>`)

- Píldoras ergonómicas (`min-height: 40px`, padding `6px 14px`, radio `50px`).
- Activo: fondo `#1c1c1a`, texto blanco. Inactivo: transparente con hover `rgba(0,0,0,0.08)`.

---

## 4. Componentes de Contención y Diálogo (Estructura)

### A. Diálogos Modales Nativos (`<dialog>`)

- **Implementación:** HTML5 `<dialog>` nativo accesible (apertura con `.showModal()`, cierre con `.close()` o `Escape`).
- **Geometría:** Ancho `400-520px` (`w-full max-w-[500px]`), fondo `#ffffff`, bordes redondeados `16px`, padding `24-28px`, backdrop `rgba(0,0,0,0.4)`.
- **Estructura Interna:** Cabecera con título H2 e ícono, cuerpo con advertencia contextual (Nielsen H5) y pie de acciones alineado a la derecha con separación mínima de `12px` (Fitts).

### B. Tarjetas Autónomas (`.tarjeta` / `.card`)

- Fondo `#ffffff`, borde `#e3e3df`, bordes redondeados `12px`, padding `16-20px`.
- Diseñadas para responder a sus dimensiones clave (qué ocurrió, cuándo, estado) sin forzar navegación secundaria.

### C. Tablas Modulares de Datos (`.tabla-contenedor`)

- Contenedor con `overflow-x: auto`, `min-width: 880px`, bordes redondeados y padding lateral de `20px`.
- Altura estándar de fila: `68px` para ritmo visual homogéneo. Celda de acciones flex alineada al final con botones de 44px.

---

## 5. Componentes de Información, Feedback y Prevención

### A. Notificaciones Flotantes Nativas (`<x-toast-alerta />`)

- Posición fija `position: fixed; top: 20px; right: 20px; z-index: 50;` (Zero Layout Shift).
- Auto-desvanecimiento en 3 segundos (3000ms), botón de cierre manual (≥ 44 px) y directiva `aria-live="polite"`.
- Variantes: Éxito (`#edf7ef` / `#25663a`), Error (`#fbeceb` / `#c0392b`).

### B. Banners de Advertencia y Freno de Emergencia (Nielsen H5 y H6)

- **Banner de Freno / Bloqueo:** Fondo `#fbeceb`, borde `#f5c6cb`, texto `#c0392b`, ícono de advertencia. Explica la razón del bloqueo (ej. deuda pendiente) y ofrece la acción de resolución directa.
- **Banner Informativo / Proceso:** Fondo `#fff4d6`, borde `#ffe683`, texto `#8a6100`. Indica procesos en segundo plano (ej. escaneo OCR de comprobante).

### C. Selector de Modo e Intención (Hick $n \le 4$)

- Selector acotado entre modo total vs parcial (ej. pagar total con 1 clic y campo bloqueado vs habilitar input manual).
- Elimina tipeo redundante cuando el sistema ya conoce el dato (Nielsen H6).

### D. Estados Vacíos Empáticos

- Contenedor centrado con ícono suave, mensaje contextual en lenguaje natural y botón guiado a la acción primaria.

---

## 6. Telemetría y Observabilidad de Interacción (Solo Dev)

### A. Dev Floating Toolbar (`dev_toolbar.blade.php`)

- Píldora discreta fija en esquina inferior derecha (`fixed bottom-4 right-4 z-50`): `[📊 Telemetría Dev · /analitica]`.
- Exclusiva para entornos `local` y `testing`. No contamina el menú de navegación del usuario común (Nielsen H8).

### B. Tarjetas de Embudo y Métricas Horizontales (`.tarjeta-embudo`)

- **Estructura y Paleta Oficial:**
    - **Tarjeta base:** Fondo blanco `surface` (`#ffffff`), borde `#e3e3df`, radio `12px` y padding `20px`.
    - **Chip identificador:** Fondo `#fff4d6` (`pending-bg`), texto `#8a6100` (`pending`), radio `9999px`.
- **Métricas Horizontales por Paso:**
    - Contadores destacados (`text-[26px] font-extrabold #1c1c1a`, `line-height: 1`).
    - Nombre técnico del evento en monospace inferior (`text-xs text-[#6f6f6a] font-mono`).
- **Caja de Finalización (`.caja-porcentaje-terminan`):**
    - Contenedor rectangular redondeado (`rounded-xl`), fondo `#edf7ef` (`success-bg`), borde `#bbf7d0` y padding `8px 14px`.
    - Porcentaje de éxito en `22px font-extrabold #25663a` (`success`) con la etiqueta `terminan` (erradicando la jerga técnica negativa de _drop-off_ en favor de métricas constructivas de éxito).
- **Barra de Avance Brand-Bar:**
    - Contenedor redondeado (`rounded-full`, altura `8px`, fondo `#f6f6f4`, borde `#e3e3df`) con relleno de token oficial `#ffe683` (`brand-bar`) mapeando la tasa de $0\%$ a $100\%$.

### C. Listado de Eventos Recientes (`Últimos 25 Eventos Registrados`)

- Tabla estructurada con fondo `#ffffff`, bordes `#e3e3df`, cabecera `#fbfbf9` y badges de evento en `#fff4d6` con texto `#8a6100`.
- Marca temporal formateada en hora oficial de Bolivia (`UTC-4`).
