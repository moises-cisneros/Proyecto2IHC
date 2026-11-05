<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmarPlanRequest;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PlanesController extends Controller
{
    /**
     * Mensajes de validación personalizados
     */
    private array $mensajes = [
        'nombre.required'       => 'Escribe el nombre del plan.',
        'organizador.required'  => 'Escribe el nombre del organizador.',
        'fecha_limite.required' => 'Elige la fecha límite.',
        'fecha_limite.date'     => 'La fecha no es válida.',
    ];

    /**
     * Listar los planes del usuario logueado y los que tiene invitación / participación
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

    /**
     * Guardar un nuevo plan
     *
     * @param  StorePlanRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function guardar(StorePlanRequest $request)
    {
        $plan = new Plan();
        $plan->nombre       = $request->nombre;
        $plan->organizador  = auth()->user()->name;
        $plan->fecha_limite = $request->fecha_limite;
        $plan->user_id      = auth()->id();
        $plan->save();

        return redirect()->route('planes.listar')->with('exito', '¡Plan creado!');
    }

    /**
     * Editar un plan existente
     *
     * @param  Plan  $plan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function editar(Plan $plan)
    {
        Gate::authorize('update', $plan);

        return view('planes.editar', compact('plan'));
    }

    /**
     * Actualizar un plan existente
     *
     * @param  UpdatePlanRequest  $request
     * @param  Plan  $plan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function actualizar(UpdatePlanRequest $request, Plan $plan)
    {
        Gate::authorize('update', $plan);

        $plan->nombre       = $request->nombre;
        $plan->fecha_limite = $request->fecha_limite;
        $plan->save();

        return redirect()->route('planes.listar')->with('exito', '¡Plan actualizado!');
    }

    /**
     * Eliminar un plan existente
     *
     * @param  Plan  $plan
     * @return \Illuminate\Http\RedirectResponse
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

    /**
     * Cambiar el estado de un plan
     *
     * @param  Request  $request
     * @param  Plan  $plan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cambiarEstado(Request $request, Plan $plan)
    {
        Gate::authorize('cambiarEstado', $plan);

        $request->validate([
            'estado' => 'required|in:pendiente,confirmado,cancelado',
        ]);

        // Regla de negocio: un plan cancelado NO puede reactivarse
        if ($plan->estado === 'cancelado' && $request->estado !== 'cancelado') {
            return redirect()->route('planes.listar')
                             ->with('error', 'Un plan cancelado no puede reactivarse.');
        }

        $plan->estado = $request->estado;
        $plan->save();

        return redirect()->route('planes.listar')
                         ->with('exito', 'El plan ahora está ' . $plan->estado . '.');
    }

    /**
     * Confirmar el plan a partir de la votación (resolviendo empates si aplica)
     *
     * @param  ConfirmarPlanRequest  $request
     * @param  Plan  $plan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirmar(ConfirmarPlanRequest $request, Plan $plan)
    {
        Gate::authorize('confirmar', $plan);

        $plan->propuesta_fecha_id = $request->propuesta_fecha_id;
        $plan->propuesta_lugar_id = $request->propuesta_lugar_id;
        $plan->motivo_desempate   = $request->motivo_desempate;
        $plan->estado             = 'confirmado';
        $plan->fecha_confirmacion = now();
        $plan->save();

        // Registrar en historial de actividades
        $descripcionHistorial = auth()->user()->name . " confirmó el plan a partir de la votación";
        if ($plan->motivo_desempate) {
            $descripcionHistorial .= " resolviendo el empate.";
        } else {
            $descripcionHistorial .= ".";
        }

        $plan->actividades()->create([
            'user_id' => auth()->id(),
            'accion' => 'estado',
            'descripcion' => $descripcionHistorial,
            'estado_resultante' => 'confirmado',
        ]);

        return redirect()->route('planes.detalle', $plan)
                         ->with('exito', '¡Plan confirmado con éxito! La votación ha concluido.');
    }
}
