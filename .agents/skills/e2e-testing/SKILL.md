---
name: e2e-testing
description: Directivas y estándares para diseño, creación y ejecución de pruebas End-to-End (E2E) con Playwright en Planazo. Usar al validar flujos completos de usuario, capturar evidencias visuales, simular tareas humanas con localizadores accesibles o depurar regresiones visuales.
license: MIT
metadata:
  author: Planazo Team
  version: "1.0"
---

# Pruebas End-to-End (E2E) con Playwright — Protocolo Planazo

> **Premisa Fundamental:**  
> *"Una prueba es una tarea humana."*  
> Las pruebas E2E no evalúan selectores de CSS ni implementaciones técnicas internas; simulan el recorrido real de una persona interactuando con la pantalla mediante elementos semánticos y accesibles.

---

## 1. Filosofía de Localizadores Semánticos y Accesibles

### 🚫 Selectores Prohibidos (Frágiles y Antipatrones)
* **Nunca** usar clases de CSS de presentación (`.boton`, `.btn-primary`, `.bg-yellow-200`, `.flex`).
* **Nunca** usar IDs arbitrarios de maquetación (`#submit-btn-2`, `#form-wrapper`).
* **Nunca** usar XPath absolutos (`/html/body/div[2]/form/button`).

### ✅ Localizadores Permitidos y Obligatorios (Accesibles)
Playwright ofrece selectores que reflejan cómo las tecnologías asistivas y los humanos perciben el DOM:

1. `page.getByRole('button', { name: 'Confirmar plan' })`
2. `page.getByRole('link', { name: 'Ver sala' })`
3. `page.getByRole('tab', { name: /Mis planes/i })`
4. `page.getByRole('dialog', { name: 'Confirmar eliminación' })`
5. `page.getByLabel('Nombre del plan')`
6. `page.getByPlaceholder('Ej. Salida al cine')`
7. `page.getByText('Plan confirmado exitosamente')`

---

## 2. Nomenclatura y Estructura de Archivos

### A. Ubicación y Nombre del Archivo (`e2e/`)
* **Flujos de Dominio Generales:**
  `e2e/<modulo>-<escenario-kebab>.spec.ts`
  * Ejemplos: `e2e/planes-creacion-flujo-completo.spec.ts`, `e2e/sala-votacion-desempate.spec.ts`.
* **Casos Específicos por Hito o Módulo:**
  `e2e/<prefijo-o-modulo>-<descripcion-kebab>.spec.ts`
  * Ejemplos: `e2e/planes-editar.spec.ts`, `e2e/historial-filtros.spec.ts`, `e2e/acceso-recuperar-contrasena.spec.ts`.

### B. Narrativa Humana del Test (`test(...)`)
Formular la descripción orientada al rol, la acción y el resultado esperado:
```typescript
test('Como organizador, puedo desempatar seleccionando la opción ganadora con motivo obligatorio', async ({ page }) => {
    // 1. Iniciar sesión como organizador
    // 2. Navegar a la sala de votación empatada
    // 3. Abrir modal de desempate
    // 4. Seleccionar opción ganadora e ingresar motivo
    // 5. Verificar que el banner de veredicto público se despliegue con la justificación
});
```

---

## 3. Comandos de Ejecución y Diagnóstico

| Intención | Comando en Host |
| :--- | :--- |
| **Ejecución completa (Headless)** | `pnpm run test:e2e` |
| **Ejecución visible (Headed)** | `pnpm run test:e2e:headed` |
| **Prueba puntual por archivo** | `pnpm run test:e2e e2e/<archivo>.spec.ts` |
| **Filtrar por patrón o título (`-g`)** | `pnpm run test:e2e:headed -g "<patrón>"` |
| **Inspector interactivo (UI Runner)** | `pnpm run test:e2e:ui` |

---

## 4. Captura de Evidencias Gráficas Automatizadas

Cuando una auditoría de interfaz o validación de flujo requiera evidencias visuales:

1. **Ruta estandarizada:** `docs/evidencias/<modulo-o-hito>/<secuencia>-<descripcion>.png`.
2. **Método en el test:**
   ```typescript
   await page.screenshot({
       path: 'docs/evidencias/planes/01-listado-pestanas.png',
       fullPage: false // o true según la necesidad del reporte
   });
   ```
3. Las capturas generadas se referencian en los documentos markdown de evidencias (`docs/evidencias/...`).

---

## 5. Aislamiento e Inmutabilidad de Datos

* **`workers: 1` obligatorio:** En `playwright.config.ts`, la ejecución es secuencial para no colisionar mutaciones en la base de datos de MySQL.
* **Autenticación limpia:** Cada test debe autenticarse explícitamente utilizando los usuarios del seeder (`moises@ejemplo.com`, `ana@ejemplo.com`, etc.) o resetear cookies/almacenamiento antes de evaluar flujos anónimos o de diferentes roles.
* **Prevención de efectos colaterales:** Evitar que una prueba dependa del orden de ejecución de otra prueba.

---

## 6. Decisiones Vigentes del Proyecto

* **Specs existentes** (`e2e/`): catalogados en `resources/FLOWS.md` §6 (creación de plan, recuperación de acceso, edición, historial, gastos, votación, analítica, salida honesta). Antes de crear uno nuevo, verificar si corresponde extender el spec del flujo existente.
* **Salida de plan:** el único flujo es «Retirarse» (`tarea-9-retirarse-plan.spec.ts`). No se prueba ni se documenta «abandonar»: ese endpoint fue eliminado.
* **Patrones oscuros:** la evidencia visual de la prueba de simetría se genera desde `clase-22-patron-oscuro.spec.ts` y `tarea-10-datos-salida-honesta.spec.ts`; criterios en la skill `dark-patterns`.
* **Fuente de los flujos a probar:** `resources/FLOWS.md` §6 (Task Flows) lista, por flujo, el spec que lo verifica; un flujo nuevo o modificado actualiza esa línea.
* **Cobertura por pantalla:** cada pantalla del App Map debe tener al menos un recorrido humano por rol (organizador, participante, invitado). Una pantalla sin spec es una brecha a registrar.
* **Localizadores en diálogos:** `getByRole('dialog', { name })` usando el título del `<dialog>` (`aria-labelledby`); nunca por `id`.
* **Specs nuevos para brechas:** `acceso-recuperar-contrasena.spec.ts` (recuperar y restablecer, incluido enlace vencido con `?email=`), `planes-editar.spec.ts` y `historial-filtros.spec.ts`.
* **Confirmar un plan en un spec:** siempre desde la sala: botón «Cerrar votación y confirmar» → diálogo `#modal-confirmar-plan` → «Confirmar y cerrar votación» → mensaje «Plan confirmado con éxito».
* **Límite de login:** `POST /ingresar` tiene `throttle:5,1`. Más de 5 inicios de sesión por minuto producen fallos falsos; al correr varios archivos seguidos, dejar ≥ 60 s entre ellos.
* **Entorno Docker:** el Vite con *polling* se satura tras editar archivos y provoca `page.goto` con timeout; esperar a que se estabilice antes de correr Playwright y no mezclar edición con ejecución.
* **Mensajes `role="status"`:** el toast flash y los banners comparten rol; filtrar por texto (`.filter({ hasText })`) para evitar violaciones de modo estricto.
* **Registrar un gasto:** el modal no preselecciona participantes; usar `marcarTodosLosParticipantes(page)` de `e2e/helpers/gastos.ts`. Los helpers compartidos van en `e2e/helpers/` (no coinciden con `*.spec.ts`).
* **Registrar un pago:** elegir la modalidad de forma explícita (`getByRole('radio', { name: 'Pago parcial' }).check()` o «Pagar totalidad»); el monto está bloqueado hasta entonces. Comprobar que ninguna esté marcada al abrir el modal.
* **Escaneo de comprobantes:** el estado «pendiente» tras «Enviar Pago» tarda en Docker; usar `toBeVisible({ timeout: 30000 })` y `test.setTimeout(180000)` en ese flujo.
* **Primer arranque tras editar archivos:** el primer `page.goto` suele agotar el tiempo (Vite en frío); repetir la corrida antes de concluir que hay un fallo.
