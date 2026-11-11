<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventoRequest;
use App\Models\Evento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventosController extends Controller
{
    /**
     * Ingesta asíncrona de eventos de interacción desde el frontend.
     */
    public function guardar(StoreEventoRequest $request): JsonResponse
    {
        $evento = Evento::registrar(
            $request->validated('evento'),
            $request->validated('props', []) ?? []
        );

        return response()->json([
            'success' => true,
            'evento' => $evento,
        ], 201);
    }

    /**
     * Despliega el panel de analítica de interacción (solo disponible en desarrollo o testing).
     */
    public function analitica(Request $request): View
    {
        abort_unless(
            app()->environment('local', 'testing') || (auth()->check() && (auth()->user()->is_admin ?? false)),
            403,
            'El panel de analítica sólo está disponible en entorno de desarrollo.'
        );

        $metricas = Evento::metricasEmbudo();
        $ultimosEventos = Evento::latest()->take(25)->get();

        return view('analitica.index', compact('metricas', 'ultimosEventos'));
    }
}
