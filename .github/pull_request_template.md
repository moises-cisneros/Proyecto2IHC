## Resumen
<!-- Describe brevemente el objetivo de este cambio y si va apilado sobre otra rama/PR -->

## Qué incluye / Cambios realizados
<!-- Detalla los cambios implementados según la capa arquitectónica -->
- **Backend / Persistencia:**
- **Controlador / Lógica:**
- **Interfaz / Blade:**

## Pruebas y Verificación
<!-- Indica las pruebas automatizadas agregadas o ejecutadas -->
- [ ] Pruebas unitarias / feature añadidas (mínimo 4 tests PHPUnit)
- [ ] Ejecución exitosa de la suite: `php artisan test`
- [ ] Verificación de interfaz / accesibilidad en navegador

## Referencias y Cierre de Tareas
<!-- REGLA OBLIGATORIA:
     - Usa Closes únicamente para el sub-issue puntual.
     - Usa Refs para el issue madre del docente (NUNCA cierres el issue del docente con Closes). -->
Closes #
Refs #

## Checklist de Calidad

- [ ] El commit y la rama están asociados al sub-issue (`feat/issue-<n>`, `Refs #<n>`)
- [ ] Cumple con la arquitectura definida en `ARCHITECTURE.md`
- [ ] **Seguridad (Auth & Policy):** Rutas protegidas por `auth`, autorización estricta en controlador con `Gate::authorize()` / `PlanPolicy` y tests de rechazo `403/302` incluidos
- [ ] Sub-issue vinculado al árbol nativo de GitHub (`gh api`)
