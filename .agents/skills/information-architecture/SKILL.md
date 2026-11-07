---
name: information-architecture
description: Directivas y metodología de Arquitectura de la Información (AI) en Planazo. Usar al estructurar navegación, categorizar vistas complejas, diseñar taxonomías por rol o urgencia temporal, definir jerarquías visuales y reducir sobrecarga cognitiva.
license: MIT
metadata:
  author: Planazo Team
  version: "1.0"
---

# Arquitectura de la Información (AI) — Protocolo Planazo

Este protocolo define las directivas y metodologías para estructurar, organizar, rotular y navegar la información en Planazo, garantizando que el usuario encuentre y procese el contenido con mínimo esfuerzo cognitivo y máxima claridad.

---

## 1. Los Cuatro Sistemas Fundamentales de Rosenfeld & Morville

Al diseñar o evaluar cualquier pantalla o flujo en Planazo, auditar y estructurar cuatro sistemas:

### 1. Sistema de Organización (Estructura de Contenidos)
* **Esquemas Exactos:**
  * **Cronológico:** Ordenamiento por fecha, recencia o urgencia de atención (ej. eventos próximos, planes en votación activa vs pasados).
  * **Alfabético:** Catálogos o listados extensos de consulta neutral.
* **Esquemas Ambiguos / Subjetivos:**
  * **Por Rol y Propósito (Task-based):** Segmentar según lo que el usuario necesita hacer (ej. «Mis planes creados» vs «Planes donde participo» vs «Invitaciones pendientes»).
  * **Por Urgencia / Estado (Topic-based):** Segmentar por nivel de deliberación («En votación activa», «Confirmados», «Cancelados o históricos»).
* **Taxonomía LATCH:**
  * Priorizar en Planazo la organización por **Categoría/Rol** y **Tiempo/Urgencia**.

### 2. Sistema de Navegación
* **Navegación Global:** Navbar superior fijo y predecible con acceso al núcleo (`Planes`, `Historial`, `Perfil/Cerrar sesión`).
* **Navegación Local y Segmentada:** Pestañas semánticas (`role="tablist"` / `role="tab"`) que aíslan contextos de decisión sin provocar saltos bruscos de página (*Zero Layout Shift*).
* **Navegación Contextual:** Enlaces y affordances directos en tarjetas y filas para profundizar hacia la sala de votación o gestión del recurso.

### 3. Sistema de Rotulado (Labeling)
* **Lenguaje del Usuario (Nielsen H2):** Usar términos familiares y directos en español neutro («Mis planes», «Invitaciones pendientes», «Sala de votación», «Confirmar fecha y lugar»).
* **Prohibición de Jerga Técnica:** Nunca exponer nombres de columnas de base de datos o estados crudos en la interfaz (`user_id`, `updated_at`, `status_code`).
* **Badges Numéricos Informativos:** Acompañar rótulos de pestañas o filtros con conteos dinámicos (ej. `Invitaciones (2)`) para evitar que el usuario deba entrar para saber si hay novedades.

### 4. Sistema de Búsqueda y Filtrado
* **Divulgación Progresiva:** Filtros visibles iniciales acotados ($\le 4$ opciones según Ley de Hick) con opción de refinar sin recargar la pantalla completa.
* **Estados Vacíos Explicativos y Guiados:** Si una búsqueda o pestaña no arroja resultados, presentar un estado vacío empático que explique la situación y ofrezca una acción inmediata de salida (ej. botón «Crear mi primer plan»).

---

## 2. Principios de Jerarquía y Carga Cognitiva

1. **Chunking (Agrupamiento Informativo):**
   * Descomponer listados planos masivos en secciones modulares con encabezados descriptivos y líneas de separación visual nítidas (`_tabla_seccion.blade.php`).
   * No superar 7 ± 2 elementos principales por grupo para no colapsar la memoria de trabajo.
2. **Jerarquía Visual y Escaneo Rápido (Scanning):**
   * El ojo del usuario debe distinguir en $< 3$ segundos: **Qué es**, **En qué estado está**, **Qué acción se espera de él**.
   * Emplear contraste tipográfico: Título en negrita $\to$ Metadatos en gris secundario (`#6f6f6a`) $\to$ Badges de estado en pasteles cálidos $\to$ Acciones alineadas al final.
3. **Coherencia y Consistencia (Nielsen H4):**
   * Misma estructura de tabla, orden de columnas y estilo de botones a lo largo de toda la aplicación (reflejado fielmente en `DESIGN.md`).

---

## 3. Checklist de Verificación para Tareas de AI

Al implementar o modificar la estructura de información de una vista:

- [ ] ¿El contenido está agrupado por el propósito real del usuario y no por cómo está guardado en la base de datos?
- [ ] ¿Las pestañas o categorías son mutuamente excluyentes y no compiten entre sí?
- [ ] ¿Los rótulos son transparentes y emplean el vocabulario del usuario?
- [ ] ¿Hay indicadores visuales (badges de conteo, colores semánticos) que anticipen el estado de la información?
- [ ] ¿Se proveen estados vacíos empáticos con llamada a la acción clara cuando no hay datos?
- [ ] ¿Los cambios estructurales están documentados y actualizados en `resources/DESIGN.md`?
