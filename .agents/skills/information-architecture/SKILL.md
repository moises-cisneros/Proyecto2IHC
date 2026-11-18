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
* **Navegación Global:** Navbar superior fijo y predecible con acceso al núcleo (`Mis Planes`, `Historial`, `Mi Cuenta`, `Salir`).
* **Navegación Local y Segmentada:** Pestañas semánticas (`role="tablist"` / `role="tab"`) que aíslan contextos de decisión sin provocar saltos bruscos de página (*Zero Layout Shift*).
* **Navegación Contextual:** Enlaces y affordances directos en tarjetas y filas para profundizar hacia la sala de votación o gestión del recurso.

### 3. Sistema de Rotulado (Labeling)
* **Lenguaje del Usuario (Nielsen H2):** Usar términos familiares y directos en español neutro («Mis planes», «Invitaciones pendientes», «Sala de votación», «Confirmar fecha y lugar»).
* **Prohibición de Jerga Técnica:** Nunca exponer nombres de columnas de base de datos o estados crudos en la interfaz (`user_id`, `updated_at`, `status_code`).
* **Badges Numéricos Informativos:** Acompañar rótulos de pestañas o filtros con conteos dinámicos (ej. `Invitaciones (2)`) para evitar que el usuario deba entrar para saber si hay novedades.

### 4. Sistema de Búsqueda y Filtrado
* **Divulgación Progresiva:** Filtros visibles iniciales acotados ($\le 4$ opciones según Ley de Hick; el reset «Todos» no cuenta como filtro: el historial muestra 4 categorías + «Todos») con opción de refinar sin recargar la pantalla completa.
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

## 3. Checklist de Verificación de Arquitectura de la Información

Al implementar o modificar la estructura de información de una vista:

- [ ] ¿El contenido está agrupado por el propósito real del usuario y no por cómo está guardado en la base de datos?
- [ ] ¿Las pestañas o categorías son mutuamente excluyentes y no compiten entre sí?
- [ ] ¿Los rótulos son transparentes y emplean el vocabulario del usuario?
- [ ] ¿Hay indicadores visuales (badges de conteo, colores semánticos) que anticipen el estado de la información?
- [ ] ¿Se proveen estados vacíos empáticos con llamada a la acción clara cuando no hay datos?
- [ ] ¿Los cambios estructurales están documentados y actualizados en `resources/DESIGN.md`?

---

## 4. Decisiones Vigentes del Proyecto

### App Map vs Task Flow (se documentan por separado)
* **App Map:** inventario de pantallas y las salidas entre ellas. Un nodo = una pantalla (ruta). Los **estados** de un recurso no son nodos del mapa.
* **Task Flow:** una secuencia por tarea de usuario (p. ej. «retirarse con deuda»), con sus ramas por rol y estado.
* No mezclar ambos en un solo diagrama.

### Fuente de verdad
El App Map, la composición de cada pantalla, la matriz de permisos y los Task Flows están en **`resources/FLOWS.md`**. Esta skill no los duplica: ante una duda, se consulta ese archivo y, si cambia una pantalla o flujo, se actualiza allí primero (`FlowsContractTest` lo verifica contra las rutas).

### Inventario vigente de pantallas
Acceso (guest): `ingresar`, `registro`, `recuperar`, `restablecer/{token}`. App (auth): `mis-planes`, `planes/{id}` (sala), `planes/{id}/editar`, `historial`, `perfil/datos` (Mi Cuenta). Dev: `analitica` (+ Dev Toolbar).

### Navegación global
Navbar: `Mis Planes`, `Historial`, `Mi Cuenta`, `Salir`. La analítica no forma parte de la navegación de usuario.

### Reglas de dominio que gobiernan la estructura
* Estados canónicos del plan: `pendiente`, `confirmado` (inmutable), `cancelado`.
* La sala es una pantalla con **varias vistas por rol**: organizador, participante, invitado pendiente (gobernadas por `PlanPolicy`).
* Salida de un plan: única vía `retirarse`, bloqueada con explicación si hay deuda.
* Eliminar plan o cuenta con bloqueos usa la «Salida Honesta»: por qué + qué hacer (`.caja-salida-honesta`).

### Checklist adicional
- [ ] ¿Toda pantalla nueva figura en el inventario y en el App Map?
- [ ] ¿Todo flujo nuevo tiene su Task Flow con ramas por rol y estado?
- [ ] ¿Los estados mostrados son solo los canónicos?

### Una acción de negocio, una sola pantalla
* Cada acción irreversible vive en **una** pantalla: confirmar un plan ocurre únicamente en la sala (votación y desempate); desde el listado solo se **enlaza** a la sala. Un botón en otra pantalla que produzca el mismo efecto saltándose reglas es un fallo de estructura, no solo de código.
* No mostrar controles deshabilitados como sustituto de una explicación: si el estado ya es visible (chip «Cancelado») y no hay acción posible, se omite el control.

### Un parámetro de URL por concepto
* La pestaña de la sala se selecciona únicamente con `?tab=` (`fechas`, `lugares`, `gastos`). El parámetro `pestana` **no existe**: los enlaces «Liquidar» de la Salida Honesta lo usaban y aterrizaban en la pestaña equivocada. Todo enlace que apunte a una pestaña usa `route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos'])`.
