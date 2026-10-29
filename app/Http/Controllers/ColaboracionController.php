<?php

namespace App\Http\Controllers;

use App\Models\Invitacion;
use App\Models\Opcion;
use App\Models\Plan;
use App\Models\User;
use App\Models\Voto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ColaboracionController extends Controller
{
    /**
     * Display the collaborative room for a plan.
     */
    public function detalle(Request $request, Plan $plan)
    {
        Gate::authorize('view', $plan);

        $plan->load([
            'user',
            'opciones.user',
            'opciones.votos',
            'invitaciones.user',
            'participantes',
        ]);

        $pestanaActiva = $request->input('tab', 'fechas');
        if (!in_array($pestanaActiva, ['fechas', 'lugares'])) {
            $pestanaActiva = 'fechas';
        }

        $fechas = $plan->opciones->where('tipo', 'fecha')->values();
        $lugares = $plan->opciones->where('tipo', 'lugar')->values();

        return view('planes.detalle', compact('plan', 'fechas', 'lugares', 'pestanaActiva'));
    }

    /**
     * Send an invitation to collaborate on a plan.
     */
    public function invitar(Request $request, Plan $plan)
    {
        Gate::authorize('invitar', $plan);

        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $usuarioExistente = User::where('email', $request->email)->first();

        $invitacion = Invitacion::firstOrNew([
            'plan_id' => $plan->id,
            'email'   => $request->email,
        ]);

        $invitacion->token   = Str::random(40);
        $invitacion->estado  = 'pendiente';
        $invitacion->user_id = $usuarioExistente?->id;
        $invitacion->save();

        return back()->with('exito', 'Invitación enviada correctamente.');
    }

    /**
     * Propose a date or location option for a plan.
     */
    public function guardarOpcion(Request $request, Plan $plan)
    {
        Gate::authorize('proponer', $plan);

        $request->validate([
            'tipo'        => 'required|in:fecha,lugar',
            'valor'       => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
        ]);

        $plan->opciones()->create([
            'user_id'     => auth()->id(),
            'tipo'        => $request->tipo,
            'valor'       => $request->valor,
            'descripcion' => $request->descripcion,
        ]);

        return back()->with('exito', 'Opción propuesta correctamente.');
    }

    /**
     * Cast or toggle a vote on an option.
     */
    public function votar(Request $request, Opcion $opcion)
    {
        Gate::authorize('votar', $opcion->plan);

        $votoExistente = Voto::where('opcion_id', $opcion->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($votoExistente) {
            $votoExistente->delete();
            $mensaje = 'Voto retirado.';
        } else {
            Voto::create([
                'opcion_id' => $opcion->id,
                'user_id'   => auth()->id(),
            ]);
            $mensaje = '¡Voto registrado!';
        }

        return back()->with('exito', $mensaje);
    }

    /**
     * Remove the authenticated user's participation and votes from the plan.
     */
    public function abandonar(Request $request, Plan $plan)
    {
        Gate::authorize('abandonar', $plan);

        $usuario = auth()->user();

        // 1. Limpiar invitaciones/membresía del usuario para este plan
        $plan->invitaciones()
            ->where(function ($q) use ($usuario) {
                $q->where('user_id', $usuario->id)
                  ->orWhere('email', $usuario->email);
            })
            ->delete();

        // 2. Limpiar todos los votos emitidos por el usuario en opciones de este plan
        $opcionIds = $plan->opciones()->pluck('id');
        Voto::whereIn('opcion_id', $opcionIds)
            ->where('user_id', $usuario->id)
            ->delete();

        return redirect()->route('planes.listar')
            ->with('exito', 'Has abandonado el plan exitosamente.');
    }
}
