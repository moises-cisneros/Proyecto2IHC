---
name: design-system-audit
description: Protocolo y directivas de auditoría del Sistema de Diseño (DESIGN.md) en Planazo. Usar para auditar vistas Blade, componentes y CSS, asegurando el cumplimiento estricto de la paleta oficial, medidas de Fitts (>= 44px), clases utilitarias aprobadas y prevención de estilos inline o librerías inventadas.
license: MIT
metadata:
  author: Planazo Team
  version: "1.0"
---

# Auditoría del Sistema de Diseño — Protocolo Planazo

> **Regla Cardinal de Diseño:**  
> *"En Planazo no se inventan clases CSS, colores arbitrarios ni librerías visuales superfluas. Todo cambio de interfaz debe reutilizar y extender la fuente de verdad consolidada en `resources/DESIGN.md`."*

---

## 1. Lista Negra de Antipatrones (Red Flags)

Cualquier PR o sub-issue que introduzca uno de los siguientes elementos debe ser rechazado o corregido de inmediato:

1. 🚫 **Librerías Visuales Externas:** Prohibido agregar Bootstrap, SweetAlert2, Flowbite, DaisyUI, NProgress o CDNs. Se emplean componentes nativos HTML5 (`<dialog>`, `<button>`, `<x-toast-alerta />`).
2. 🚫 **Emojis Crudos en Vistas:** Prohibido insertar emojis (`📅`, `📍`, `🚫`, etc.) en código Blade. Violan Nielsen H2, H4 y H8. Se exige el componente vectorial accesible `<x-icono nombre="..." />`.
3. 🚫 **Colores Arbitrarios Fuera de la Paleta:** Prohibido usar clases Tailwind con colores arbitrarios no catalogados (ej. `bg-purple-600`, `text-blue-500`). Solo se admiten los tokens semánticos de `DESIGN.md` (`brand-bar`, `primary`, `secondary`, `destructive`, `success`, `pending`).
4. 🚫 **Violación de Fitts en Áreas Táctiles:** Prohibido crear botones interactivos con altura táctil $< 44$ px o ubicar botones antagónicos («Confirmar» vs «Cancelar») con separación $< 12$ px (`gap-3`).
5. 🚫 **Estilos en Línea Rígidos (`style="width: 104px; ..."`):** Prohibido maquetar con CSS inline. Toda regla debe residir en clases semánticas o utilitarias en `app.css`.

---

## 2. Comandos de Auditoría Automatizada con Ripgrep (`rg`)

Antes de cerrar una sub-issue de interfaz o enviar un PR, ejecutar los siguientes comandos para auditar el código:

### A. Detección de Emojis Crudos
```bash
rg "[\x{1F300}-\x{1F9FF}]" resources/views/
```
*(Si devuelve coincidencias, deben sustituirse por `<x-icono nombre="..." />`).*

### B. Detección de Estilos en Línea Arbitrarios
```bash
rg -n "style=" resources/views/
```
*(Verificar que no contengan reglas de layout o dimensiones que deban ser clases utilitarias).*

### C. Detección de Colores Arbitrarios
```bash
rg -n "text-\[#|bg-\[#|border-\[#" resources/views/
```
*(Comprobar que los códigos hexadecimales coincidan exactamente con la paleta de `DESIGN.md`).*

### D. Verificación de Áreas Táctiles en Botones
```bash
rg -n "class=.*boton" resources/views/
```
*(Asegurar que incluyan `.boton`, `.boton-claro`, `.boton-rojo` o clases con `min-h-[44px]`).*

---

## 3. Matriz de Verificación Paso a Paso

Al evaluar o implementar una vista:

| Paso | Dimensión Auditada | Criterio de Aprobación en Código |
| :--- | :--- | :--- |
| **1** | **Tokens de Color** | Reutiliza los tokens oficiales (`#ffe683`, `#60605f`, `#c0392b`, `#25663a`, `#8a6100`, `#f6f6f4`, `#ffffff`). |
| **2** | **Ergonomía Fitts** | Botones $\ge 44\times 44$ px, separación antagónica $\ge 12$ px (`gap-3`). |
| **3** | **Ergonomía Hick** | Agrupación en pestañas/chips cuando $n > 4$. Desambiguación léxica obligatoria. |
| **4** | **Componentes Nativos** | Uso de `<dialog>` con `showModal()`, `<x-toast-alerta />` flotante y `<x-icono />` SVG. |
| **5** | **Feedback y Estados** | Chips de estado con texto e ícono semántico (Nielsen H1), sin comunicar estado solo con color. |
| **6** | **Telemetría Dev** | Paneles técnicos de desarrollo aislados fuera del navbar del usuario común (`dev_toolbar.blade.php`). |

---

## 4. Checklist de Aprobación para Agentes

- [ ] ¿El archivo modificado respeta el sistema agnóstico de componentes de `resources/DESIGN.md`?
- [ ] ¿Se verificó la ausencia de emojis crudos en plantillas Blade?
- [ ] ¿Se verificaron las áreas de contacto táctil mínimas (≥ 44 px) en todos los elementos interactivos?
- [ ] ¿Se reutilizaron las clases de botones oficiales (`.boton`, `.boton-claro`, `.boton-rojo`)?
- [ ] ¿Los modales emplean el elemento nativo `<dialog>` sin dependencias externas?
