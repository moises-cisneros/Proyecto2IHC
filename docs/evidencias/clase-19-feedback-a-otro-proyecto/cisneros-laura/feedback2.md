# Feedback a otro proyecto - Clase 19

- Nombre completo del evaluador: Moises David Cisneros Laura
- Mi grupo y caso de estudio: Planazo - Planificación colaborativa de actividades grupales
- Grupo y caso de estudio: Aranibar - Presupuesto Estudiantil
- Integrantes del proyecto estudiado: Carmen Jenifer Aranibar Fernandez
- Url de la app y repositorio evaluado: <https://github.com/Carmen21-A/FORK-PROYECTO2-IHC>
- Fecha y version/commit probado (si esta disponible): 04/11/2026 - Commit `d9281aabed84b2b83e44cb4c6da4fc4d73b01863`

## Primera impresion

¿Que entendi al entrar? ¿Que me confundio?

- **Lo que entendí:** La plataforma está orientada a la gestión y control presupuestario mensual para estudiantes universitarios. La pantalla principal presenta con claridad un resumen financiero superior (límite del mes, gasto acumulado y saldo restante reflejados en una barra de progreso visual), complementado abajo con un listado discriminado de movimientos (gastos en rojo e ingresos en verde).
- **Lo que me confundió:** Al intentar filtrar de inmediato por una categoría recurrente (ej. "Comida"), el filtro no está accesible en la vista principal; se encuentra oculto dentro de un botón desplegable denominado `"Más filtros"`, lo que agrega clics innecesarios y resta visibilidad al estado actual de filtrado. Asimismo, en la tabla de movimientos, los indicadores de estado (`pendiente` / `pagado`) lucen como simples etiquetas de texto pasivas (badges informativos), sin affordance visual que comunique que son botones accionables que conmutan el estado del registro al pulsarlos.

## Funcionalidad asignada y resultado

Describir la tarea indicada por el docente:
**Registrar un gasto con categoría y localizarlo mediante la herramienta de filtrado.**

Marcar solo una opcion:

- [x] La complete sin ayuda
- [ ] La complete con ayuda. ¿Cual?
- [ ] No pude completarla. ¿Donde me detuve?

*(Nota: La creación del gasto de 25.50 Bs en la categoría "Comida" ("Almuerzo") se completó con éxito e incrementó de inmediato el gasto total de 40 a 65.50 Bs, funcionando el buscador por texto; no obstante, se evidenció fricción ergonómica al buscar categorías específicas debido al menú colapsado y la falta de feedback persistente).*

## Segunda funcionalidad evaluada (Iniciativa propia del evaluador)

Describir la tarea elegida por el evaluador:
**Definir un presupuesto límite por categoría y evaluar el consumo visual del período.**

Marcar solo una opcion:

- [x] La complete sin ayuda
- [ ] La complete con ayuda. ¿Cual?
- [ ] No pude completarla. ¿Donde me detuve?

*(Observación del flujo: La asignación de 200 Bs a la categoría "Comida" calculó correctamente el 33% de consumo con su barra de progreso en verde; sin embargo, la navegación temporal entre meses y la ausencia de métricas históricas de referencia generaron fricción de uso).*

## Que no funciono?

Pasos, resultado esperado y resultado observado.
Si todo funciono, indicarlo. Adjuntar captura si ayuda.

1. **Ocultamiento de categorías y falta de persistencia en filtros activos (Funcionalidad asignada - Nielsen H1 y Ley de Hick):**
   - **Pasos:** Iniciar sesión -> Ingresar a la lista de movimientos -> Intentar filtrar exclusivamente los gastos de "Comida".
   - **Resultado esperado:** Filtros rápidos por categorías visibles en la pantalla principal o indicadores/chips persistentes que señalen qué filtros se encuentran aplicados.
   - **Resultado observado:** El acceso a las categorías exige abrir obligatoriamente el menú desplegable "Más filtros". Al cerrar dicho menú o navegar en la lista, no existe ningún indicador o badge permanente que informe al usuario que la vista se encuentra filtrada, arriesgando interpretaciones erróneas del balance visualizado.

   ![Filtro de categoría dentro de Más filtros](img/01-filtro-categoria.png)

2. **Ausencia de acción de restauración en estado vacío de búsqueda (Funcionalidad asignada - Nielsen H9 y Ley de Fitts):**
   - **Pasos:** Escribir en el buscador un término sin coincidencias (ej. "Fotocopias").
   - **Resultado esperado:** Despliegue de un estado vacío informativo que incluya un botón destacado ("Limpiar búsqueda" o "Restablecer filtros") centrado en el área de contenido principal.
   - **Resultado observado:** Se presenta el mensaje de texto informativo ("No hay movimientos que coincidan con la búsqueda"), pero carece de un botón de acción en el cuerpo central. Obliga al usuario a desplazarse hacia un icono de cruz pequeño ubicado dentro de un chip superior, aumentando el tiempo de puntería y la carga motriz según la Ley de Fitts.

   ![Búsqueda vacía con chip pequeño para limpiar](img/02-busqueda-vacia.png)

3. **Navegación temporal rígida y costosa entre períodos (Segunda funcionalidad - Nielsen H3 y H7):**
   - **Pasos:** Navegar a "Presupuestos por Categoría" -> Retroceder varios meses mediante los controles de flechas `<` y `>`.
   - **Resultado esperado:** Disponibilidad de un atajo o botón rápido ("Mes actual" o "Hoy") para regresar inmediatamente al período en curso.
   - **Resultado observado:** El sistema carece de un acceso directo al mes actual. Si el usuario retrocede varios períodos para auditar gastos pasados, está obligado a pulsar reiteradamente la flecha de avance mes a mes tantas veces como períodos haya retrocedido, penalizando la eficiencia de navegación.

   ![Pantalla de Presupuesto por Categoría y selector de mes](img/03-presupuesto-categoria.jpg)

4. **Falta de referencia contextual y sobrecarga de memoria de trabajo (Segunda funcionalidad - Nielsen H6):**
   - **Pasos:** Abrir el formulario para establecer un nuevo presupuesto de categoría.
   - **Resultado esperado:** Visualizar un dato orientativo del consumo real del período anterior o un promedio sugerido para apoyar la toma de decisiones.
   - **Resultado observado:** El campo numérico se presenta en blanco sin referencias históricas, forzando al usuario a memorizar o calcular mentalmente su gasto habitual en esa categoría antes de definir el nuevo límite.

*(Aspecto que funcionó bien: El registro y actualización inmediata de totales tras ingresar un nuevo movimiento, así como el cálculo porcentual dinámico de la barra de progreso al fijar un presupuesto por categoría, respondieron de forma precisa y sin fallos).*

## ¿Que mejoraria y porque?

Una propuesta concreta relacionada con la experiencia.

1. **Implementación de chips de categorías directos y botón de restauración en estados vacíos (Ley de Hick, Ley de Fitts y Nielsen H9):**
   - **Problema:** Ocultar los filtros de categorías más frecuentes detrás del menú "Más filtros" añade pasos innecesarios para la tarea principal del estudiante, mientras que la falta de un CTA de restauración en búsquedas sin resultados entorpece la recuperación del usuario.
   - **Propuesta:** Incorporar una barra de chips de acceso rápido con las categorías principales (Comida, Transporte, etc.) directamente debajo del buscador. Asimismo, ante búsquedas sin resultados, agregar en el cuerpo central un botón prominente "Restablecer búsqueda" para retornar al listado completo con un solo clic.
2. **Atajo al período vigente e información contextual previa en presupuestos (Nielsen H3, H6 y H7):**
   - **Problema:** La navegación temporal paso a paso ralentiza el retorno al mes en curso y la ausencia de datos históricos obliga a recordar cifras de memoria.
   - **Propuesta:** Incluir un botón de retorno rápido ("Mes actual") junto al selector temporal y mostrar dentro del formulario de presupuesto una referencia del gasto real incurrido en el mes anterior en dicha categoría como guía contextual.
3. **Claridad de affordance interactiva en estados de movimientos (Nielsen H1 y H2):**
   - **Problema:** Los botones de `pendiente` / `pagado` se confunden visualmente con etiquetas de texto pasivas.
   - **Propuesta:** Diseñar los controles de estado con affordance explícita (estilo botón conmutable o selector segmentado con microinteracción) para comunicar claramente que son elementos interactivos.

## ¿Que quitaria y porque?

Si no quitaria nada, explicar porque.

1. **Quitar el modal bloqueante de confirmación para eliminar o cancelar movimientos:**
   - **Por qué:** Desplegar una ventana modal emergente para confirmar acciones comunes como cancelar o eliminar interrumpe innecesariamente el ritmo de uso (Nielsen H8). Resulta más ergonómico y ágil ejecutar la acción de manera inmediata y mostrar una notificación toast flotante no intrusiva con la opción "Deshacer" disponible durante 5 segundos, protegiendo al usuario contra errores accidentales sin bloquear la interfaz.

   ![Cartel emergente modal que interrumpe la interacción](img/04-modal-eliminar.png)
