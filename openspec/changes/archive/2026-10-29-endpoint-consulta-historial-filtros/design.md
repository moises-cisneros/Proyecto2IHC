# Design: Endpoint y Consulta Cronológica de Historial con Filtros Temáticos

## 1. Contexto y Objetivos

El objetivo de este diseño es proveer la capa de controlador y rutas web para la consulta del historial de auditoría de actividad en Planazo (sub-issue #15, Refs #1), asegurando:
- **Aislamiento Multi-inquilino / Seguridad:** Ningún usuario puede ver registros pertenecientes a otro usuario (`user_id = auth()->id()`).
- **Ergonomía Cognitiva (Ley de Hick):** Filtros temáticos limitados a $n=4$ alternativas (`todos`, `estados`, `creaciones`, `participacion`), minimizando el tiempo de decisión cognitiva $T = b \log_2(n+1)$.
- **Orden Cronológico Descendente:** Los eventos más recientes se presentan primero (`latest()`).

## 2. Definición del Endpoint y Rutas

- **Ruta:** `GET /historial`
- **Nombre de ruta:** `historial.index`
- **Middleware:** `auth` (redirige usuarios invitados a `/ingresar`)
- **Parámetros Query permitidos:**
  - `categoria`: `todos` (default), `estados`, `creaciones`, `participacion`.

## 3. Lógica del Controlador: `HistorialController`

```php
public function index(Request $request)
{
    $categoria = $request->input('categoria') ?? $request->input('filtro') ?? 'todos';

    $query = PlanActivity::with('plan')
        ->where('user_id', auth()->id());

    if ($categoria === 'estados') {
        $query->where('accion', 'cambio_estado');
    } elseif ($categoria === 'creaciones') {
        $query->where('accion', 'creacion');
    } elseif ($categoria === 'participacion') {
        $query->whereIn('accion', ['invitacion', 'voto', 'propuesta', 'participacion']);
    }

    $actividades = $query->latest()->get();

    if ($request->wantsJson()) {
        return response()->json([
            'categoria' => $categoria,
            'actividades' => $actividades,
        ]);
    }

    return view('planes.historial', compact('actividades', 'categoria'));
}
```

## 4. Estrategia de Pruebas Automatizadas

Suite `tests/Feature/HistorialConsultaTest.php` (PHPUnit):
1. `test_usuario_no_autenticado_no_puede_consultar_historial`: Redirección 302 a `/ingresar`.
2. `test_usuario_solo_ve_sus_propias_actividades_en_el_historial`: Aislamiento entre Usuario A y Usuario B.
3. `test_filtro_por_categoria_estados_retorna_solo_cambios_de_estado`: Validación del filtro temático `estados`.
4. `test_actividades_se_retornan_en_orden_cronologico_descendente`: Verificación de ordenación cronológica (`latest()`).
5. `test_filtro_por_categoria_creaciones_retorna_solo_creaciones`: Validación del filtro temático `creaciones`.
6. `test_filtro_por_categoria_participacion_retorna_eventos_participativos`: Validación de eventos colaborativos.
