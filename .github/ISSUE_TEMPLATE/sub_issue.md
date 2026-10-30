---
name: Sub-issue Atómico
about: Tarea atómica perteneciente a una issue madre (backend, controlador, interfaz o evidencias)
title: '[Capa] — [Descripción concisa de la tarea] (Refs #[Madre])'
labels: ['sub-issue']
assignees: ''
---

## Issue Madre Asociada

- **Refs #** <!-- Número de la issue madre asignada por el docente -->

## Capa / Ámbito de la Tarea
<!-- Seleccionar la capa correspondiente -->
- [ ] Backend / Persistencia (Modelo, migraciones, relaciones Eloquent, reglas de dominio)
- [ ] Controlador / Lógica (Endpoints, validación de Form Requests, queries, filtros)
- [ ] Interfaz / Blade (Vistas Blade, componentes, estilos Tailwind, estados vacíos, accesibilidad)
- [ ] Pruebas y Evidencias (Tests unitarios PHPUnit, auditoría IHC, capturas en docs/)

## Objetivo
<!-- Qué problema resuelve o qué comportamiento concreto implementa este sub-issue -->

## Criterios de Aceptación

- [ ]
- [ ]

## Verificación de Seguridad y Control de Acceso (Autenticación y Autorización)
<!-- Obligatorio para todo sub-issue con rutas, controladores, datos o mutaciones -->
- [ ] **Autenticación (Identidad):** Endpoint/ruta protegido bajo middleware `auth` (redirección 302 ante accesos anónimos).
- [ ] **Autorización (Permisos / Menor Privilegio):** Verificación formal mediante Policy (`Gate::authorize()`) en backend y `@can` en Blade para asegurar que usuarios ajenos o sin privilegios queden bloqueados.
- [ ] **Prueba de Seguridad (Caso Negativo):** Test automatizado que verifique explícitamente el rechazo con `403 Forbidden` ante intentos de acceso o mutación indebida.

## Pruebas Automatizadas (PHPUnit)
<!-- Obligatorio mínimo 4 casos de prueba si involucra reglas de dominio, transiciones de estado o controladores (incluyendo al menos 1 caso negativo de autorización 403) -->
- [ ] Caso 1:
- [ ] Caso 2:
- [ ] Caso 3:
- [ ] Caso 4 (Seguridad / Rechazo 403 o 302):
