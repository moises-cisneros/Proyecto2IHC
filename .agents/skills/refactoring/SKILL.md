---
name: refactoring
description: Protocolo y directivas de refactorización limpia y segura en Planazo (Laravel/Blade/PHP). Usar al simplificar controladores sobrecargados, descomponer vistas Blade monolíticas, extraer lógica de dominio o eliminar code smells sin alterar contratos ni romper pruebas.
license: MIT
metadata:
  author: Planazo Team
  version: "1.0"
---

# Refactorización Limpia y Segura — Protocolo Planazo

> **Regla Cardinal de Refactorización (Martin Fowler):**  
> *"Refactorizar es disciplinadamente cambiar la estructura interna del software para hacerlo más fácil de entender y más económico de modificar, **sin alterar su comportamiento observable externo**."*

---

## 1. Regla de Oro: Seguridad y Cobertura Previa

1. **Nunca refactorizar sin una red de seguridad:**
   * Antes de modificar una sola línea de código, debe existir una suite de pruebas que valide el comportamiento actual (PHPUnit en backend y Playwright en frontend).
   * Si la funcionalidad no tiene pruebas suficientes, **escribir primero pruebas de caracterización** que capturen el comportamiento actual antes del refactor.
2. **Ciclo Disciplinado:**
   * Ejecutar la suite: `docker compose exec planazo_app php artisan test` (debe estar en verde).
   * Aplicar una transformación atómica pequeña (ej. extraer método, extraer parcial Blade).
   * Volver a ejecutar los tests inmediatamente.
   * Si todo pasa, commitear con mensaje semántico (`refactor(...)`).
3. **No mezclar refactoring con nuevas funcionalidades:**
   * En un commit o PR de refactorización está **estrictamente prohibido** agregar features de negocio. Se limpia la estructura primero; la nueva funcionalidad se agrega en un commit o sub-issue posterior.

---

## 2. Catálogo de Code Smells Frecuentes en Planazo

| Code Smell | Síntoma en el Código | Solución / Técnica de Refactor |
| :--- | :--- | :--- |
| **God Controller / Long Method** | Métodos de controlador de $> 30$ líneas que mezclan validación, queries, autorización y redirecciones. | Extraer validación a **Form Requests** (`app/Http/Requests/`), autorización a **Policies** (`app/Policies/`) y queries complejas a **Scopes de Eloquent**. |
| **Monolithic Blade View** | Archivo `.blade.php` de $> 250$ líneas que mezcla cabecera, tabla, modales y formularios. | Descomponer en parciales semánticos atómicos (`_cabecera.blade.php`, `_tabla_seccion.blade.php`, `_participantes.blade.php`). |
| **Feature Envy (Envidia de Atributos)** | Un controlador inspecciona repetidamente los atributos de un modelo para tomar decisiones de negocio (ej. `$plan->user_id === auth()->id()`). | Mover la lógica al Modelo (`$plan->esOrganizador(User $user)`) o delegar a una **Policy**. |
| **Duplicated Code (Código Duplicado)** | Misma consulta de filtro, formato de fecha o estructura de tabla repetida en varios controladores o vistas. | Centralizar en **Scopes locales** de Eloquent o componentes Blade reutilizables (`resources/views/components/`). |
| **Primitive Obsession** | Uso indiscriminado de strings y arrays asociativos para representar estados y roles del dominio. | Encapsular en constantes de modelo o Enums tipados de PHP 8.2+. |

---

## 3. Patrones de Refactorización Específicos para Laravel

### A. Desacoplamiento de Controladores
* **Validación:**
  ```php
  // ANTES: Validación directa dentro del controlador
  $request->validate(['titulo' => 'required|max:255', 'descripcion' => 'nullable']);

  // DESPUÉS: Form Request desacoplado con autorización integrada
  public function store(CrearPlanRequest $request) { ... }
  ```
* **Autorización:**
  ```php
  // ANTES: Condicionales manuales que arrojan abort(403)
  if ($plan->user_id !== auth()->id()) { abort(403); }

  // DESPUÉS: Policy nativa
  $this->authorize('update', $plan);
  // O en Blade:
  @can('update', $plan) ... @endcan
  ```

### B. Descomposición de Vistas Blade
* Un archivo contenedor orquestador (ej. `listar.blade.php` o `show.blade.php`) solo debe incluir submódulos mediante directivas `@include` pasándoles explícitamente sus variables:
  ```blade
  @include('planes._cabecera', ['plan' => $plan])
  @include('planes._participantes', ['participantes' => $participantes])
  @include('planes._sala_votacion', ['plan' => $plan, 'fechas' => $fechas])
  ```
* **Componentes Blade Autocontenidos:**
  * Para elementos transversales (`<dialog>`, botones, toasts), crear componentes en `resources/views/components/` (ej. `<x-toast-alerta />`).

---

## 4. Checklist de Verificación Post-Refactor

- [ ] ¿Todos los tests existentes pasan al 100% sin modificar sus aserciones?
- [ ] ¿El comportamiento observable en la interfaz sigue siendo exactamente el mismo?
- [ ] ¿Se redujo la complejidad ciclomática de los métodos intervenidos?
- [ ] ¿Las nuevas clases o componentes respetan el principio de responsabilidad única (SRP)?
- [ ] ¿El commit utiliza la convención semántica `refactor(...)` sin atribuciones de IA?
