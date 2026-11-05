# Diseño: Confirmación de Plan, Resolución de Empates y Mejoras por Feedback

## 1. Arquitectura de Validación y Controladores (`app/Http/Requests/`)

### Form Requests Desacoplados

Para aislar la lógica de validación de los controladores y cumplir con el estándar arquitectónico de Laravel:

- **`StorePlanRequest`:**
  - `nombre`: `['required', 'string', 'max:255']`
  - `fecha_limite`: `['required', 'date', 'after_or_equal:today']`
  - Mensajes descriptivos en español, especialmente `fecha_limite.after_or_equal => 'La fecha del plan debe ser igual o posterior a la fecha actual.'`.
- **`UpdatePlanRequest`:**
  - Idéntica regla para `fecha_limite`, protegiendo la edición contra regresiones a fechas pasadas.
- **`ConfirmarPlanRequest`:**
  - `propuesta_fecha_id`: `['required', 'exists:opciones,id']` con regla condicional de que pertenezca al plan actual y su tipo sea `'fecha'`.
  - `propuesta_lugar_id`: `['nullable', 'exists:opciones,id']` asegurando pertenencia al plan.
  - `motivo_desempate`: validación condicional. Si `$plan->tieneEmpateFechas() || $plan->tieneEmpateLugares()`, la regla exige `['required', 'string', 'min:5']`.

---

## 2. Persistencia y Lógica de Dominio (`app/Models/Plan.php` y Migración)

### Migración de Base de Datos

```php
Schema::table('planes', function (Blueprint $table) {
    $table->foreignId('propuesta_fecha_id')->nullable()->after('estado')->constrained('opciones')->nullOnDelete();
    $table->foreignId('propuesta_lugar_id')->nullable()->after('propuesta_fecha_id')->constrained('opciones')->nullOnDelete();
    $table->text('motivo_desempate')->nullable()->after('propuesta_lugar_id');
    $table->timestamp('fecha_confirmacion')->nullable()->after('motivo_desempate');
});
```

### Métodos de Modelo Eloquent

- **Relaciones:**

  ```php
  public function propuestaFechaGanadora(): BelongsTo
  {
      return $this->belongsTo(Opcion::class, 'propuesta_fecha_id');
  }

  public function propuestaLugarGanadora(): BelongsTo
  {
      return $this->belongsTo(Opcion::class, 'propuesta_lugar_id');
  }
  ```

- **Detección de Empates:**

  ```php
  public function tieneEmpateFechas(): bool
  {
      $conteoVotos = $this->opciones()->where('tipo', 'fecha')->withCount('votos')->pluck('votos_count')->sortDesc()->values();
      if ($conteoVotos->count() < 2) return false;
      return $conteoVotos[0] > 0 && $conteoVotos[0] === $conteoVotos[1];
  }

  public function obtenerPropuestasEmpatadas(string $tipo)
  {
      $opciones = $this->opciones()->where('tipo', $tipo)->withCount('votos')->get();
      $maxVotos = $opciones->max('votos_count');
      if (!$maxVotos || $maxVotos === 0) return collect();
      return $opciones->where('votos_count', $maxVotos)->values();
  }
  ```

---

## 3. Autorización y Política de Seguridad (`app/Policies/PlanPolicy.php`)

```php
public function confirmar(User $user, Plan $plan): bool
{
    // Solo el organizador/creador del plan puede confirmar
    if (!$this->isCreator($user, $plan)) {
        return false;
    }

    // No se puede confirmar un plan cancelado o ya confirmado
    return $plan->estado === 'pendiente';
}
```

---

## 4. Diseño de Interfaz y Ergonomía (IHC)

### Selector de Fechas con Confirmación Explícita (H3 / H5)

- En `_sala_votacion.blade.php`, se añade un botón explícito `Aceptar fecha` junto al campo `<input type="datetime-local">`.
- Al pulsar «Aceptar fecha», un handler JavaScript guarda el valor en el estado del formulario, actualiza un indicador visual (`Fecha confirmada: 15/12/2026 18:00`) y previene pérdidas accidentales por pérdida de foco.

### Formulario de Creación y Edición (H5 / H9)

- En `_modal_crear.blade.php` y `editar.blade.php`, se asigna el atributo `min="{{ now()->format('Y-m-d') }}"` al input `fecha_limite`.
- Se mantiene el renderizado de errores accesible `@error('fecha_limite')` con color de contraste y mensaje claro.

### Modal de Cierre y Desempate (`<dialog>`)

- En planes pendientes, el organizador dispone del botón `Cerrar votación y confirmar` protegido con `@can('confirmar', $plan)`.
- Si no hay empate: muestra la propuesta líder seleccionada automáticamente.
- Si hay empate: la Ley de Hick se aplica mostrando exclusivamente las opciones empatadas para reducir opciones innecesarias ($T = b \log_2(n+1)$).
- Campo de justificación obligatorio: `<textarea name="motivo_desempate" required placeholder="Explica por qué elegiste esta opción en caso de empate (mínimo 5 caracteres)...">`.

### Banner de Veredicto Público e Inmutabilidad

- En planes confirmados, se muestra en la cabecera de la sala un banner prominente:
  - Opción de fecha confirmada.
  - Opción de lugar confirmada (si existe).
  - Justificación pública: *«Decisión del organizador: [Motivo registrado]»*.
- Los botones de votar se encuentran desactivados (`disabled`), y proponer opciones está oculto.

---

## 5. Estrategia de Testing (PHPUnit & Playwright)

- **Sub-issue #46:** Pruebas Feature de validación en `tests/Feature/ValidacionFechasPlanTest.php` comprobando rechazo de fechas pasadas en creación, edición y redirección de accesos no autenticados.
- **Sub-issue #47:** Pruebas Unit de persistencia en `tests/Unit/PersistenciaDesempateTest.php` comprobando relaciones, detección de empates y protección de asignaciones.
- **Sub-issue #50:** Pruebas Feature de confirmación y autorización en `tests/Feature/ConfirmarPlanTest.php` comprobando cierre exitoso, validación de motivo en empates y rechazo `403 Forbidden` a terceros.
- **Sub-issue #48:** Pruebas Feature de Blade en `tests/Feature/VistasConfirmacionDesempateTest.php` verificando visibilidad de modal bajo `@can` y renderizado de veredicto.
- **Sub-issue #49:** Pruebas E2E en Playwright y documentación en `evidencias/tarea-7.md`.
