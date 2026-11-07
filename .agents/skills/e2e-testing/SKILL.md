---
name: e2e-testing
description: Directivas y estándares para diseño, creación y ejecución de pruebas End-to-End (E2E) con Playwright en Planazo. Usar al validar flujos completos de usuario, capturar evidencias visuales, simular tareas humanas con localizadores accesibles o depurar regresiones visuales.
license: MIT
metadata:
  author: Planazo Team
  version: "1.0"
---

# Pruebas End-to-End (E2E) con Playwright — Protocolo Planazo

> **Premisa Pedagógica Fundamental:**  
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
* **Casos Asignados por Tarea de Clase o Feedback:**
  `e2e/<prefijo-de-tarea>-<descripcion-kebab>.spec.ts`
  * Ejemplos: `e2e/tarea-7-confirmacion-desempate.spec.ts`, `e2e/feedback-1-selector-fechas.spec.ts`.

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

Cuando una tarea docente o entrega requiera evidencias visuales del flujo:

1. **Ruta estandarizada:** `docs/evidencias/<nombre-entrega>/<secuencia>-<descripcion>.png`.
2. **Método en el test:**
   ```typescript
   await page.screenshot({
       path: 'docs/evidencias/clase-20/01-listado-pestanas.png',
       fullPage: false // o true según la necesidad del reporte
   });
   ```
3. Las capturas generadas se referencian en los documentos markdown de evidencias (`docs/evidencias/...`).

---

## 5. Aislamiento e Inmutabilidad de Datos

* **`workers: 1` obligatorio:** En `playwright.config.ts`, la ejecución es secuencial para no colisionar mutaciones en la base de datos de MySQL.
* **Autenticación limpia:** Cada test debe autenticarse explícitamente utilizando los usuarios del seeder (`moises@ejemplo.com`, `ana@ejemplo.com`, etc.) o resetear cookies/almacenamiento antes de evaluar flujos anónimos o de diferentes roles.
* **Prevención de efectos colaterales:** Evitar que una prueba dependa del orden de ejecución de otra prueba.
