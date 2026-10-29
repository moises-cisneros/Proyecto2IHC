# Design: Automatización del Organizador por Sesión y Badge de Solo Lectura

## Contexto y Motivación
El diagnóstico arquitectónico (`docs/internal/InformeTarea5.md`, Decisión 3) y la especificación visual en `DESIGN.md` establecen que el formulario de creación de planes infringía las heurísticas Nielsen H6 (Reconocimiento antes que recuerdo) y H8 (Diseño estético y minimalista). El sistema obligaba al usuario a escribir manualmente su nombre como organizador a pesar de estar autenticado en la sesión de Laravel.

## Decisiones Técnicas

### 1. Desacoplamiento del Modal en Parcial Blade (`_modal_crear.blade.php`)
- Para cumplir con la arquitectura monolítica modular definida en `AGENTS.md` (sección 9), se extrae el `<dialog id="modal-crear">` de `resources/views/planes/listar.blade.php` hacia `resources/views/planes/_modal_crear.blade.php`.
- En `listar.blade.php` se incluye de forma limpia: `@include('planes._modal_crear')`.

### 2. Badge Informativo no Editable (`DESIGN.md`)
- Se remueve:
  ```blade
  <div class="campo">
      <label for="organizador">Organizador</label>
      <input type="text" id="organizador" name="organizador" value="{{ old('organizador') }}" placeholder="Nombre del organizador">
      @error('organizador')
          <span class="campo-error">{{ $message }}</span>
      @enderror
  </div>
  ```
- Se sustituye por un badge estático informativo:
  ```blade
  <div class="campo-badge-organizador">
      <span class="organizador-etiqueta">
          <svg class="icono-usuario" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
          </svg>
          Organizador: <strong>{{ auth()->user()->name }}</strong>
      </span>
  </div>
  ```
- Estilos aplicados (`DESIGN.md` tokens):
  - Contenedor con borde `#e3e3df`, fondo suave `#f6f6f4`, radio `8px`, padding `10px 14px`, texto `#6f6f6a` y font-size `14px`.

### 3. Asignación Automática en `PlanesController::guardar`
- Se elimina `'organizador' => 'required|max:255'` del arreglo de validación.
- Mensaje `'organizador.required'` pasa a ser obsoleto para el guardado.
- En la instanciación del modelo:
  ```php
  $plan = new Plan();
  $plan->nombre       = $request->nombre;
  $plan->organizador  = auth()->user()->name;
  $plan->fecha_limite = $request->fecha_limite;
  $plan->user_id      = auth()->id();
  $plan->save();
  ```
- Se preserva la columna `organizador` en base de datos garantizando compatibilidad con el esquema original.

### 4. Cobertura con Pruebas Automatizadas (`tests/Feature/CreacionPlanTest.php`)
- Se crea la suite con 5 casos de prueba:
  1. `test_usuario_autenticado_crea_plan_con_organizador_automatico`: Petición POST sin campo `organizador` almacena en base de datos el nombre del usuario autenticado.
  2. `test_creacion_plan_falla_sin_nombre`: Petición sin `nombre` genera error de validación en sesión.
  3. `test_creacion_plan_falla_sin_fecha_limite`: Petición sin `fecha_limite` genera error de validación en sesión.
  4. `test_usuario_no_autenticado_no_puede_crear_plan`: Petición sin sesión redirige a `/ingresar`.
  5. `test_vista_mis_planes_muestra_badge_organizador_y_omite_input`: La vista `mis-planes` contiene el badge de organizador y carece de `<input name="organizador"`.

## Verificación de Impacto
- No existen cambios destructivos en las tablas de MySQL.
- Los tests existentes continúan en verde (`docker exec planazo_app php artisan test`).
- La verificación de sub-issue valida la ausencia de menciones de IA y conformidad con el estándar del proyecto.

