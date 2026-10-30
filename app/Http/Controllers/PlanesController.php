<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PlanesController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | \
    /
    |--------------------------------------------------------------------------
    */
    private array $mensajes = [
        'nombre.required'       => 'Escribe el nombre del plan.',
        'organizador.required'  => 'Escribe el nombre del organizador.',
        'fecha_limite.required' => 'Elige la fecha límite.',
        'fecha_limite.date'     => 'La fecha no es válida.',
    ];

    /*
    |--------------------------------------------------------------------------
    \/
    /
    |--------------------------------------------------------------------------
    */
    public function listar(Request $request)
    {
        // Leemos los filtros del query string (?buscar=...&estado=...&fecha=...)
        $buscar = $request->input('buscar', '');
        $estado = $request->input('estado', '');
        $fecha  = $request->input('fecha', '');

        $userId = auth()->id();
        $userEmail = auth()->user()->email;

        // Traemos los planes del usuario logueado o en los que tiene invitación / participación
        $planes = Plan::where(function ($query) use ($userId, $userEmail) {
            $query->where('user_id', $userId)
                  ->orWhereHas('invitaciones', function ($q) use ($userId, $userEmail) {
                      $q->where('user_id', $userId)
                        ->orWhere('email', $userEmail);
                  });
        })
        ->filtrar($buscar, $estado, $fecha)
        ->orderBy('fecha_limite', 'asc')
        ->get();

        return view('planes.listar', compact('planes', 'buscar', 'estado', 'fecha'));
    }

    /*
    |--------------------------------------------------------------------------
    | /
    /
    |--------------------------------------------------------------------------
    */
    public function guardar(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|max:255',
            'fecha_limite' => 'required|date',
        ], $this->mensajes);

        $plan = new Plan();
        $plan->nombre       = $request->nombre;
        $plan->organizador  = auth()->user()->name;
        $plan->fecha_limite = $request->fecha_limite;
        $plan->user_id      = auth()->id();
        $plan->save();

        return redirect()->route('planes.listar')->with('exito', '¡Plan creado!');
    }

    /*
    |--------------------------------------------------------------------------
    | 
    /
    |--------------------------------------------------------------------------
    */
    public function editar(Plan $plan)
    {
        Gate::authorize('update', $plan);

        return view('planes.editar', compact('plan'));
    }

    /*
    |--------------------------------------------------------------------------
    | /
    /
    |--------------------------------------------------------------------------
    */
    public function actualizar(Request $request, Plan $plan)
    {
        Gate::authorize('update', $plan);

        $request->validate([
            'nombre'       => 'required|max:255',
            'organizador'  => 'required|max:255',
            'fecha_limite' => 'required|date',
        ], $this->mensajes);

        $plan->nombre       = $request->nombre;
        $plan->organizador  = $request->organizador;
        $plan->fecha_limite = $request->fecha_limite;
        $plan->save();

        return redirect()->route('planes.listar')->with('exito', '¡Plan actualizado!');
    }

    /*
    |--------------------------------------------------------------------------
    |
    /
    |--------------------------------------------------------------------------
    */
    public function eliminar(Plan $plan)
    {
        Gate::authorize('delete', $plan);

        // Regla de negocio: no se puede eliminar un plan confirmado
        if ($plan->estado === 'confirmado') {
            return redirect()->route('planes.listar')
                             ->with('error', 'Un plan confirmado no puede eliminarse. Primero cancélalo.');
        }

        $plan->delete();

        return redirect()->route('planes.listar')->with('exito', 'Plan eliminado.');
    }

    /*
    |--------------------------------------------------------------------------
    | 
    \
    |--------------------------------------------------------------------------
    */
    public function cambiarEstado(Request $request, Plan $plan)
    {
        Gate::authorize('cambiarEstado', $plan);

        $request->validate([
            'estado' => 'required|in:pendiente,confirmado,cancelado',
        ]);

        //  un plan cancelado NO puede volver a confirmarse
        if ($plan->estado === 'cancelado' && $request->estado === 'confirmado') {
            return redirect()->route('planes.listar')
                             ->with('error', 'Un plan cancelado no puede volver a confirmarse.');
        }

        $plan->estado = $request->estado;
        $plan->save();

        return redirect()->route('planes.listar')
                         ->with('exito', 'El plan ahora está ' . $plan->estado . '.');
    }
}
