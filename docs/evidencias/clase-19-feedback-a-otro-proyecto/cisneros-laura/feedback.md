# Feedback a otro proyecto - Clase 19

- Nombre completo del evaluador: Moises David Cisneros Laura
- Mi grupo y caso de estudio: Planazo - Planificación colaborativa de actividades grupales
- Grupo y caso de estudio: Unisport - Presupuesto Estudiantil / Gestión deportiva universitaria
- Integrantes del proyecto estudiado: Alvaro Sonco Guzman, Juan Carlos Laura Cespedes
- Url de la app y repositorio evaluado: <https://github.com/alvaroSG34/Segundo-Proyecto-IHC.git>
- Fecha y version/commit probado (si esta disponible): 03/11/2026 - Commit `b034d7cbf084add2beace624ab99a0d092cc5222` (Prueba local/despliegue con usuario `juan@gmail.com`).

## Primera impresion

¿Que entendi al entrar? ¿Que me confundio?

- **Lo que entendí:** La plataforma está orientada a la gestión y participación en partidos deportivos universitarios. La presentación visual inicial es agradable, limpia y el formulario de inicio de sesión funciona de forma rápida y correcta.
- **Lo que me confundió:** En la vista del login se encuentra visible toda la barra de navegación superior (navbar) del sistema interno. Si bien al pulsar sobre cualquiera de los enlaces el sistema redirige al login (lo que demuestra que los middlewares de autenticación funcionan), el componente del **navbar no debería estar público ni visible** en la pantalla de acceso. Al no existir una landing page informativa limpia o un layout de acceso aislado, mostrar el navbar completo a usuarios no autenticados genera confusión, ruido visual innecesario y rompe la separación formal entre la interfaz pública de acceso y el panel interno.

## Funcionalidad asignada y resultado

Describir la tarea indicada por el docente:
**Buscar un partido con cupos disponibles e inscribirse.**

Marcar solo una opcion:

- [x] La complete sin ayuda
- [ ] La complete con ayuda. ¿Cual?
- [ ] No pude completarla. ¿Donde me detuve?

_(Nota: Aunque la inscripción se completó sin requerir asistencia externa, se detectó fricción considerable en la pérdida de foco y confirmación post-inscripción)._

## Segunda funcionalidad evaluada (Iniciativa propia del evaluador)

Describir la tarea elegida por el evaluador:
**Consultar los jugadores inscritos en un partido y gestionar la salida del equipo (desinscripción).**

Marcar solo una opcion:

- [x] La complete sin ayuda
- [ ] La complete con ayuda. ¿Cual?
- [ ] No pude completarla. ¿Donde me detuve?

_(Observación del flujo: La acción de salir del equipo / desinscribirse funcionó de forma correcta y fluida; sin embargo, la consulta previa de jugadores inscritos presentó problemas ergonómicos y de diseño de interfaz)._

## Que no funciono?

Pasos, resultado esperado y resultado observado.
Si todo funciono, indicarlo. Adjuntar captura si ayuda.

1. **Pérdida de foco y contexto tras la inscripción (Funcionalidad asignada - Nielsen H1):**
    - **Pasos:** Iniciar sesión -> Navegar a la búsqueda de partidos -> Ubicar un partido con cupos libres -> Presionar el botón para inscribirse.
    - **Resultado esperado:** Retroalimentación inmediata clara (toast o alert focalizado) manteniendo la pantalla posicionada sobre el partido seleccionado, o redirigiendo al usuario al detalle de "Mis Partidos".
    - **Resultado observado:** El sistema redirige automáticamente al inicio de la búsqueda general sin ningún tipo de anclaje, scroll o resalte sobre el partido registrado. El usuario tiene que buscar nuevamente entre la lista completa de partidos para constatar si su registro fue exitoso, generando esfuerzo motriz y carga cognitiva innecesarios.

2. **Modal a pantalla completa sobredimensionado para jugadores inscritos (Segunda funcionalidad - Nielsen H8):**
    - **Pasos:** Ubicar el partido inscrito -> Pulsar en "Ver jugadores inscritos".
    - **Resultado esperado:** Un modal compacto, popover o panel lateral proporcionado al volumen real de información.
    - **Resultado observado:** Se despliega un modal gigante que bloquea toda la pantalla, ocupando un área desmedida para mostrar una lista mínima de nombres, descontextualizando al usuario de la vista principal.

3. **Banner de alerta invasivo en la parte inferior:**
    - **Pasos:** Ejecutar acciones dentro de la aplicación que detonan notificaciones de estado.
    - **Resultado esperado:** Toasts discretos, auto-descartables y no bloqueantes en una esquina.
    - **Resultado observado:** La alerta se fija ocupando todo el ancho de la zona inferior de la pantalla, compitiendo con el contenido y obstaculizando la interacción con elementos inferiores de la interfaz.

_(Aspecto que funcionó bien: La acción de desinscripción / "Salir del equipo" dentro de la segunda funcionalidad funcionó correctamente y sin errores)._

## ¿Que mejoraria y porque?

Una propuesta concreta relacionada con la experiencia.

1. **Diseño de un componente unificado de Card con jerarquía visual clara (Ley de Hick y Nielsen H8):**
    - **Problema:** En las tarjetas de partidos de las distintas disciplinas existe una fuerte competencia visual entre títulos, etiquetas de deportes, fechas y botones de acción. La información compite por el foco del usuario y la paleta de colores luce opaca y sin lineamientos consistentes.
    - **Propuesta:** Crear un componente modular y estándar para las tarjetas de partidos adaptado a cada deporte pero con jerarquía fija: cabecera con disponibilidad de cupos en badge contrastado, datos esenciales (cancha, horario) en tipografía neutra y un único botón principal (CTA) prioritario ("Inscribirme"). Reducir la sobrecarga cognitiva agrupando o relegando datos secundarios.
2. **Definición de un Design System y paleta de colores definida:**
    - Establecer una paleta institucional estandarizada: un color primario con ratio de contraste óptimo para acciones, tonos neutros para tarjetas/fondos y colores semánticos (verde/rojo) reservados exclusivamente para disponibilidad de cupos y alertas de error.

## ¿Que quitaria y porque?

Si no quitaria nada, explicar porque.

1. **Quitar el Navbar completo en la pantalla de login y vistas públicas:**
    - **Por qué:** No se trata únicamente de que los enlaces redirijan al login al ser pulsados (lo cual ya hacen), sino de la presencia misma del componente. El navbar completo no debe renderizarse para visitantes anónimos. La vista de login debe emplear un layout limpio e independiente (o una landing institucional previa), eliminando el navbar para evitar ruido visual y falsas expectativas de navegación antes de autenticarse.
2. **Quitar el modal a pantalla completa para la lista de jugadores inscritos:**
    - **Por qué:** Ocupar la totalidad del viewport para una lista breve de nombres es un desperdicio visual que rompe la continuidad de la tarea. Debe sustituirse por un diálogo modal pequeño y centrado o un acordeón colapsable en la propia tarjeta.
