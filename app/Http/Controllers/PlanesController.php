<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmarPlanRequest;
use App\Http\Requests\RetirarsePlanRequest;
use App\Http\Requests\StorePlanRequest;
use App\Http\Requests\UpdatePlanRequest;
use App\Models\Evento;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PlanesController extends Controller
{
    /**
     * Mensajes de validación personalizados
     */
    private array $mensajes = [
        'nombre.required' => 'Escribe el nombre del plan.',
        'organizador.required' => 'Escribe el nombre del organizador.',
        'fecha_limite.required' => 'Elige la fecha límite.',
        'fecha_limite.date' => 'La fecha no es válida.',
    ];

    /**
     * Listar los planes segmentados por rol (organizador vs invitado) y agrupados por temporalidad.
     */
    public function listar(Request $request)
    {
        $userId = auth()->id();
        $userEmail = auth()->user()->email;

        // Leemos los filtros adicionales del query string (?buscar=...&estado=...&fecha=...)
        $buscar = $request->input('buscar', '');
        $estado = $request->input('estado', '');
        $fecha = $request->input('fecha', '');

        // Segmentación por propósito/rol: 'creados' (default) o 'invitaciones'
        // Si no se especifica y el usuario no tiene planes creados pero sí invitaciones, orienta a 'invitaciones'
        if ($request->filled('seccion') && in_array($request->input('seccion'), ['creados', 'invitaciones'], true)) {
            $seccion = $request->input('seccion');
        } else {
            $tieneCreados = Plan::where('user_id', $userId)->exists();
            $seccion = $tieneCreados ? 'creados' : 'invitaciones';
        }

        // Consulta filtrada según la sección seleccionada
        if ($seccion === 'invitaciones') {
            $query = Plan::whereHas('invitaciones', function ($q) use ($userId, $userEmail) {
                $q->where('user_id', $userId)
                    ->orWhere('email', $userEmail);
            });
        } else {
            $query = Plan::where('user_id', $userId);
        }

        $planes = $query->filtrar($buscar, $estado, $fecha)
            ->orderBy('fecha_limite', 'asc')
            ->get();

        // Agrupación temporal por urgencia respecto a hoy
        $hoyFecha = today();

        $vencidos = $planes->filter(function ($plan) use ($hoyFecha) {
            return $plan->fecha_limite && $plan->fecha_limite->lt($hoyFecha);
        })->values();

        $hoy = $planes->filter(function ($plan) use ($hoyFecha) {
            return $plan->fecha_limite && $plan->fecha_limite->equalTo($hoyFecha);
        })->values();

        $proximos = $planes->filter(function ($plan) use ($hoyFecha) {
            return $plan->fecha_limite && $plan->fecha_limite->gt($hoyFecha);
        })->values();

        $contadores = [
            'vencidos' => $vencidos->count(),
            'hoy' => $hoy->count(),
            'proximos' => $proximos->count(),
        ];

        $totalVencidos = $vencidos->count();
        $totalHoy = $hoy->count();
        $totalProximos = $proximos->count();

        if ($buscar !== '' && $planes->isEmpty()) {
            Evento::registrar('busqueda_sin_resultados', [
                'termino' => $buscar,
                'seccion' => $seccion,
                'estado' => $estado,
                'fecha' => $fecha,
            ]);
        }

        return view('planes.listar', compact(
            'planes',
            'vencidos',
            'hoy',
            'proximos',
            'contadores',
            'totalVencidos',
            'totalHoy',
            'totalProximos',
            'seccion',
            'buscar',
            'estado',
            'fecha'
        ));
    }

    /**
     * Guardar un nuevo plan
     *
     * @return RedirectResponse
     */
    public function guardar(StorePlanRequest $request)
    {
        $plan = new Plan;
        $plan->nombre = $request->nombre;
        $plan->organizador = auth()->user()->name;
        $plan->fecha_limite = $request->fecha_limite;
        $plan->user_id = auth()->id();
        $plan->save();

        Evento::registrar('plan_guardado', [
            'longitud_nombre' => strlen($plan->nombre),
            'tiene_fecha_limite' => ! empty($plan->fecha_limite),
        ]);

        return redirect()->route('planes.listar')->with('exito', '¡Plan creado!');
    }

    /**
     * Editar un plan existente
     *
     * @return RedirectResponse
     */
    public function editar(Plan $plan)
    {
        Gate::authorize('update', $plan);

        return view('planes.editar', compact('plan'));
    }

    /**
     * Actualizar un plan existente
     *
     * @return RedirectResponse
     */
    public function actualizar(UpdatePlanRequest $request, Plan $plan)
    {
        Gate::authorize('update', $plan);

        $plan->nombre = $request->nombre;
        $plan->fecha_limite = $request->fecha_limite;
        $plan->save();

        return redirect()->route('planes.listar')->with('exito', '¡Plan actualizado!');
    }

    /**
     * Eliminar un plan existente
     *
     * @return RedirectResponse
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
     * @return RedirectResponse
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
            ->with('exito', 'El plan ahora está '.$plan->estado.'.');
    }

    /**
     * Confirmar el plan a partir de la votación (resolviendo empates si aplica)
     *
     * @return RedirectResponse
     */
    public function confirmar(ConfirmarPlanRequest $request, Plan $plan)
    {
        Gate::authorize('confirmar', $plan);

        $plan->propuesta_fecha_id = $request->propuesta_fecha_id;
        $plan->propuesta_lugar_id = $request->propuesta_lugar_id;
        $plan->motivo_desempate = $request->motivo_desempate;
        $plan->estado = 'confirmado';
        $plan->fecha_confirmacion = now();
        $plan->save();

        // Registrar en historial de actividades
        $descripcionHistorial = auth()->user()->name.' confirmó el plan a partir de la votación';
        if ($plan->motivo_desempate) {
            $descripcionHistorial .= ' resolviendo el empate.';
        } else {
            $descripcionHistorial .= '.';
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

    /**
     * Retirar al usuario autenticado como participante del plan.
     */
    public function retirarse(RetirarsePlanRequest $request, Plan $plan): RedirectResponse
    {
        Gate::authorize('retirarse', $plan);

        $userId = auth()->id();

        // Si el usuario tiene saldos pendientes deudores en gastos, bloquear el retiro
        if ($plan->usuarioTieneDeuda($userId)) {
            Evento::registrar('error_freno_tarea', [
                'motivo' => 'deuda_pendiente',
                'plan_id' => $plan->id,
                'user_id' => $userId,
            ]);
            Evento::registrar('retiro_bloqueado_deuda', [
                'plan_id' => $plan->id,
                'user_id' => $userId,
            ]);

            return redirect()->back()
                ->with('error', 'No puedes retirarte del plan mientras tengas deudas pendientes por liquidar.');
        }

        $plan->retirarParticipante(auth()->user());

        Evento::registrar('participante_retirado', [
            'plan_id' => $plan->id,
            'user_id' => $userId,
        ]);
        Evento::registrar('retiro_confirmado', [
            'plan_id' => $plan->id,
            'user_id' => $userId,
        ]);

        return redirect()->route('planes.listar')
            ->with('exito', 'Te has retirado del plan exitosamente.');
    }
}
