---
name: dark-patterns
description: Protocolo permanente de prevención y auditoría de patrones oscuros (deceptive patterns) en Planazo. Usar al diseñar, modificar o revisar cualquier flujo, vista Blade, modal, formulario, copy o regla de negocio donde el usuario decide, acepta, paga, se une o se va. Aplica la prueba de simetría, el catálogo de 6 patrones y comandos rg de detección.
license: MIT
metadata:
  author: Planazo Team
  version: "1.0"
---

# Patrones Oscuros — Protocolo Planazo

> **Regla cardinal:**
> *"Salir tiene que ser tan fácil como entrar: misma cantidad de toques, mismo peso visual, texto honesto sin chantaje emocional y consecuencias reales explicadas sin trucos."*

**Definición operativa:** un patrón oscuro esconde, confunde o cansa al usuario para que no elija la otra opción; inventa urgencia o culpa; y funciona porque el usuario no entiende. La alternativa es la **persuasión honesta**: informar, ordenar con claridad y dejar que decida.

Esta skill es **transversal**. Se consulta en cualquier cambio donde el usuario decide algo, no solo en flujos de «salida honesta».

---

## 1. Cuándo activar esta skill

Activarla si el cambio toca alguno de estos puntos de decisión:

| Punto de decisión | Ejemplos en Planazo |
| :--- | :--- |
| **Entrar / aceptar** | Registro, aceptar invitación, unirse a un plan, votar |
| **Salir / revertir** | Retirarse, rechazar invitación, cancelar o eliminar plan, eliminar cuenta |
| **Pagar / comprometer dinero** | Registrar gasto, dividir, liquidar deuda, subir comprobante |
| **Confirmar algo irreversible** | Confirmar plan, desempate, eliminar, rechazar pago |
| **Copy y estados** | Textos de botones, avisos, banners, estados vacíos, bloqueos |
| **Datos personales** | Exportar, editar, eliminar datos, telemetría |

Si el cambio no toca ninguno, no hace falta auditar.

---

## 2. Las cuatro preguntas clave de decisión honesta (filtro rápido)

Antes de cerrar un cambio, responder en voz alta:

1. **¿Qué botón se ve primero y por qué?** Si el primario siempre empuja hacia lo que conviene al sistema, sospechar.
2. **¿Qué siente el usuario al leer la opción alternativa?** Si genera culpa, vergüenza o miedo, es confirmshaming.
3. **¿Cuántos pasos lleva entrar vs. salir?** Salir no puede costar más pasos que entrar.
4. **¿A quién beneficia este diseño?** Si la respuesta no es el usuario, corregir.

---

## 3. Prueba de simetría (criterio de aprobación principal)

Toda acción con contraparte (entrar/salir, aceptar/rechazar, activar/desactivar) debe cumplir los cuatro ejes:

| Eje | Criterio | Referencia en código |
| :--- | :--- | :--- |
| **Pasos** | Mismos clics/toques para ambos sentidos | Task Flow en `resources/FLOWS.md` §6 |
| **Peso visual** | Mismo tamaño, tipografía y prominencia en ambos botones | `.boton-claro` + `.boton-rojo` (`DESIGN.md` §3.A, §7.B) |
| **Tono** | Texto neutro, sin reproches ni emojis de culpa | Copy del modal |
| **Consecuencias** | Se explican antes de actuar, con datos concretos | `.caja-salida-honesta` (`DESIGN.md` §7.A) |

Ergonomía asociada (skill `interaction-decisions`): altura táctil **≥ 44 px**, separación antagónica **≥ 12 px**, sin raíz léxica compartida entre botones opuestos.

---

## 4. Catálogo de patrones, detección y corrección

Los seis patrones catalogados en la auditoría de Planazo:

### 4.1 Opciones preseleccionadas (*pre-ticked / default opt-in*)
- **Señal:** `checked` por defecto en checkbox o radio que condiciona dinero, datos o participación.
- **Corrección:** el usuario elige de forma consciente; sin default que beneficie al sistema o empuje una decisión económica.
- **Ejemplo en repo:** participantes de un gasto y modalidad de pago en `planes/_gastos_liquidacion.blade.php`. Test: `tests/Feature/EticaGastosPatronOscuroTest.php`.

### 4.2 Confirmaciones engañosas (*confirmshaming* / desbalance)
- **Señal:** copy que culpa («No, prefiero perderme el plan»), `confirm()` nativo sin contexto, botón de salida en estilo secundario frente a uno de permanencia llamativo.
- **Corrección:** modal `<dialog>` con consecuencias objetivas y botonera simétrica. Componente: `<x-modal-salida-honesta />` (`DESIGN.md` §7.B).

### 4.3 Urgencia, culpa o escasez falsas
- **Señal:** temporizadores artificiales, «quedan 2 lugares», «última oportunidad» que no provienen de datos reales.
- **Corrección:** solo mostrar fechas límite y cupos que existan en la lógica de negocio (`fecha_limite` del organizador). Prohibido inventar presión.

### 4.4 Obstrucción (*roach motel*: fácil entrar, difícil salir)
- **Señal:** botón `disabled` sin explicación; invitación que solo se puede aceptar; imposibilidad de darse de baja.
- **Corrección:** todo bloqueo explica **por qué** y ofrece **qué hacer** (`.caja-salida-honesta`); toda entrada tiene su salida simétrica (aceptar ⇄ rechazar, unirse ⇄ retirarse, crear cuenta ⇄ eliminar cuenta).
- **Tests de referencia:** `tests/Feature/SalidaHonesta*Test.php`, `tests/Unit/SalidaHonestaDominioTest.php`.

### 4.5 Costos o consecuencias ocultas
- **Señal:** acción que genera un compromiso (deuda, bloqueo de salida) sin avisarlo antes de confirmar.
- **Corrección:** mensaje preventivo antes de la acción, con la consecuencia concreta (p. ej. «mientras debas, no podrás retirarte»).

### 4.6 Redirección de atención (*visual interference*)
- **Señal:** la acción de permanencia destaca y la de salida se esconde en gris, texto pequeño o enlace sin forma de botón.
- **Corrección:** misma altura, prominencia y separación en ambas; solo difiere el significado semántico (claro vs. destructivo).

### 4.7 Patrones a vigilar aunque aún no existan en Planazo
*Nagging* (insistencia repetida), *forced continuity* (cobro tras prueba), *sneaking* (añadir algo al flujo sin avisar), *hidden costs*, *trick wording* (doble negación). Si un cambio los introduce, tratarlos como los seis anteriores.

---

## 5. Comandos de detección con `rg`

Ejecutar sobre las vistas y JS antes de cerrar un cambio:

```bash
# 4.1 Preseleccionados (revisar cada coincidencia: ¿condiciona dinero, datos o participación?)
rg -n "\bchecked\b" resources/views/

# 4.1 Preselección por JavaScript o por clase visual «activa» (el atributo HTML no basta)
rg -n "\.checked\s*=\s*true|\.selected\s*=\s*true|--activa" resources/views/ resources/js/ public/js/

# 4.2 Confirmaciones nativas genéricas
rg -n "data-confirm|confirm\(" resources/views/ resources/js/

# 4.3 Urgencia artificial
rg -n -i "countdown|setInterval|quedan [0-9]|últim[oa]s? (lugar|cupo|oportunidad)|solo hoy" resources/views/ resources/js/

# 4.4 Botones deshabilitados (¿explican el motivo y la salida?)
rg -n "\bdisabled\b" resources/views/

# 4.2/4.6 Copy con culpa o desbalance
rg -n -i "no gracias|prefiero (perder|no)|renunciar|me rindo|abandonar" resources/views/

# Pares de acción: ¿existe la contraparte? (aceptar ⇄ rechazar)
rg -n "aceptar|rechazar|retirarse|abandonar|eliminar" resources/views/ routes/web.php
```

El resultado es una **lista de sospechas**, no un veredicto: cada coincidencia se evalúa con la prueba de simetría.

---

## 6. Flujo de trabajo (Reconocer → Encontrar → Corregir)

1. **Reconocer:** recorrer el flujo afectado con las cuatro preguntas (§2) y la prueba de simetría (§3).
2. **Encontrar:** ejecutar los `rg` (§5) y clasificar cada hallazgo con el patrón del catálogo (§4).
3. **Capturar el antes:** evidencia en `docs/evidencias/.../patron-<n>-antes.png`.
4. **Corregir en código:** reutilizar componentes de `resources/DESIGN.md` (no inventar clases). Escribir primero el test que falla (RED) cuando sea verificable.
5. **Capturar el después:** `.../patron-<n>-despues.png` y justificar en una línea qué principio se restituyó.
6. **Registrar:** si el patrón o la regla es nueva, actualizar el catálogo (§4) y `DESIGN.md` si cambia un componente.

---

## 7. Relación con otras skills

| Skill | Qué aporta a esta |
| :--- | :--- |
| `interaction-decisions` | Fitts (≥ 44 px, ≥ 12 px) y Hick (desambiguación léxica) para la simetría |
| `heuristic-evaluation` | Severidad 0–4 y H5 (prevención), H6 (reconocimiento), H8 (minimalismo) para registrar hallazgos |
| `design-system-audit` | Garantiza que la corrección use tokens y componentes oficiales |
| `information-architecture` | Estados vacíos y bloqueos con salida guiada |
| `e2e-testing` | Evidencia visual y flujos Playwright de salida honesta (`e2e/tarea-10-datos-salida-honesta.spec.ts`) |

---

## 8. Checklist de aprobación

- [ ] ¿Toda acción de entrada tiene una salida con los mismos pasos?
- [ ] ¿Los botones opuestos tienen igual altura (≥ 44 px), prominencia y separación (≥ 12 px)?
- [ ] ¿El copy es neutro, sin culpa, miedo ni emojis?
- [ ] ¿Los controles que implican dinero, datos o participación están sin preseleccionar?
- [ ] ¿Cada bloqueo explica el motivo y ofrece la acción para resolverlo?
- [ ] ¿Las fechas límite y cupos mostrados provienen de datos reales?
- [ ] ¿Las consecuencias (deuda, bloqueo, irreversibilidad) se avisan **antes** de confirmar?
- [ ] ¿Se ejecutaron los comandos `rg` del §5 y se evaluó cada sospecha?
- [ ] ¿Hay test (negativo incluido) y evidencia antes/después del cambio?

---

## 9. Decisiones Vigentes del Proyecto

* **Aceptar sin poder rechazar:** en cualquier pantalla donde se ofrezca «Aceptar invitación» debe estar «Rechazar invitación» con la misma altura y separación (el listado de planes lo omitía; ahora son simétricos).
* **Una sola salida de plan:** `planes.retirarse`. Existió un endpoint `abandonar` que evadía la regla de deuda y fue eliminado: una ruta paralela más permisiva que la oficial es en sí misma una forma de incoherencia ética (la regla de «salida honesta» solo vale si no hay atajos).
* **Bloqueo por deuda es informado, no oculto:** el retiro bloqueado explica el motivo y enlaza a liquidar (patrón 4.4/4.5). Toda salida bloqueada debe seguir ese molde.
* **Confirmar un plan solo en la sala:** el atajo del listado que confirmaba sin votación ni desempate se eliminó (misma lógica que la salida única: una ruta paralela más permisiva es un patrón de obstrucción inversa). El listado enlaza a la sala.
* **Confirmaciones destructivas:** todo `data-confirm` que cancele o elimine lleva `data-type="danger"` para que el diálogo use `.boton-rojo` en la acción y `.boton-claro` en «Mantener» (`public/js/ui.js`).
* **Sin controles deshabilitados mudos:** un botón `disabled` solo con `title` (tooltip) no es explicación suficiente; se omite o se reemplaza por el diálogo de Salida Honesta.
* **Modalidad de pago sin preselección, ni por JavaScript:** al abrir el modal de pago ninguna modalidad está marcada (`establecerModalidadNeutral()`), el monto queda vacío y bloqueado hasta elegir «Pagar totalidad» o «Pago parcial», y ambas opciones son `required`. Un `radio.checked = true` en JS es el mismo patrón que un `checked` en el HTML.
* **Un test que no encuentra el elemento no demuestra nada:** una aserción de ausencia (`assertDontSee`) debe ir acompañada de otra de presencia del componente que se inspecciona; si no, el test pasa aunque la vista ni siquiera lo renderice.
