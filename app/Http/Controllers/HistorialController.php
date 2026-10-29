<?php

namespace App\Http\Controllers;

use App\Models\PlanActivity;
use Illuminate\Http\Request;

class HistorialController extends Controller
{
    /**
     * Muestra la lista cronológica del historial de actividad del usuario autenticado.
     */
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
}
