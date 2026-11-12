<?php

namespace App\Http\Controllers;

use App\Contracts\ComprobanteScannerInterface;
use App\Http\Requests\RechazarPagoRequest;
use App\Http\Requests\StoreGastoRequest;
use App\Http\Requests\StorePagoRequest;
use App\Models\Evento;
use App\Models\Gasto;
use App\Models\GastoDivision;
use App\Models\Pago;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class GastosController extends Controller
{
    /**
     * Registrar un nuevo gasto en el plan con división equitativa o personalizada.
     */
    public function guardarGasto(StoreGastoRequest $request, Plan $plan)
    {
        Gate::authorize('create', [Gasto::class, $plan]);

        $montoTotal = round((float) $request->monto_total, 2);
        $participantesIds = collect($request->participantes)->map(fn ($id) => (int) $id)->values();

        $gasto = $plan->gastos()->create([
            'pagador_id' => auth()->id(),
            'concepto' => $request->concepto,
            'monto_total' => $montoTotal,
            'tipo_division' => $request->tipo_division,
        ]);

        if ($request->tipo_division === 'equitativa') {
            $count = $participantesIds->count();
            $cuotaBase = floor(($montoTotal / $count) * 100) / 100;
            $diferencia = round($montoTotal - ($cuotaBase * $count), 2);

            foreach ($participantesIds as $index => $uid) {
                // Asignar centavos restantes al primer participante para cuadrar la suma exacta
                $montoAsignado = $cuotaBase;
                if ($index === 0 && $diferencia > 0) {
                    $montoAsignado = round($montoAsignado + $diferencia, 2);
                }

                GastoDivision::create([
                    'gasto_id' => $gasto->id,
                    'user_id' => $uid,
                    'monto_asignado' => $montoAsignado,
                ]);
            }
        } else {
            // Personalizada
            $divisiones = $request->input('divisiones', []);
            foreach ($participantesIds as $uid) {
                $monto = isset($divisiones[$uid]) ? round((float) $divisiones[$uid], 2) : 0.0;
                GastoDivision::create([
                    'gasto_id' => $gasto->id,
                    'user_id' => $uid,
                    'monto_asignado' => $monto,
                ]);
            }
        }

        // Trazabilidad en historial de actividades
        $plan->actividades()->create([
            'user_id' => auth()->id(),
            'accion' => 'gasto',
            'descripcion' => auth()->user()->name." registró el gasto '{$gasto->concepto}' por Bs {$gasto->monto_total}.",
            'estado_resultante' => $plan->estado,
        ]);

        Evento::registrar('gasto_registrado', [
            'plan_id' => $plan->id,
            'gasto_id' => $gasto->id,
            'monto' => $montoTotal,
            'tipo_division' => $request->tipo_division,
        ]);

        return redirect()->route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos'])
            ->with('exito', '¡Gasto registrado con éxito!');
    }

    /**
     * Registrar un pago a favor de un acreedor (nace en estado pendiente).
     */
    public function guardarPago(StorePagoRequest $request, Plan $plan)
    {
        Gate::authorize('create', [Pago::class, $plan]);

        $montoFloat = round((float) $request->monto, 2);

        $saldos = $plan->calcularSaldosNetos();
        $deuda = collect($saldos['deudas'] ?? [])->first(function ($d) use ($request) {
            return (int) $d['deudor_id'] === (int) auth()->id() && (int) $d['acreedor_id'] === (int) $request->receptor_id;
        });

        if ($deuda && $montoFloat > ((float) $deuda['monto'] + 0.009)) {
            return back()->withErrors(['monto' => "El monto ingresado (Bs {$montoFloat}) excede la deuda exigible de Bs {$deuda['monto']}."]);
        }

        $datosPago = [
            'deudor_id' => auth()->id(),
            'receptor_id' => $request->receptor_id,
            'monto' => $montoFloat,
            'estado' => 'pendiente',
            'estado_escaneo' => 'sin_comprobante',
        ];

        if ($request->hasFile('comprobante')) {
            $archivo = $request->file('comprobante');
            $nombreLimpio = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $archivo->getClientOriginalName());
            $nombreArchivo = time().'_'.$nombreLimpio;
            $rutaAlmacenada = $archivo->storeAs('comprobantes', $nombreArchivo, 'public');
            $datosPago['comprobante_path'] = $rutaAlmacenada;

            /** @var ComprobanteScannerInterface $scanner */
            $scanner = app(ComprobanteScannerInterface::class);
            $rutaAbsoluta = Storage::disk('public')->path($rutaAlmacenada);
            $resultado = $scanner->scan($rutaAbsoluta);

            $datosPago['monto_detectado'] = $resultado->monto;
            $datosPago['banco_detectado'] = $resultado->banco;
            $datosPago['confianza_escaneo'] = $resultado->confianza;
            $datosPago['detalles_escaneo'] = $resultado->rawPayload;
            $datosPago['estado_escaneo'] = $resultado->determinarEstadoEscaneo($montoFloat);
        }

        $pago = $plan->pagos()->create($datosPago);

        $nombreReceptor = $pago->receptor?->name ?? 'destinatario';

        // Trazabilidad
        $plan->actividades()->create([
            'user_id' => auth()->id(),
            'destinatario_id' => $request->receptor_id,
            'accion' => 'pago',
            'descripcion' => auth()->user()->name." registró un pago de Bs {$pago->monto} a {$nombreReceptor} (pendiente de confirmación).",
            'estado_resultante' => $plan->estado,
        ]);

        Evento::registrar('pago_iniciado', [
            'plan_id' => $plan->id,
            'pago_id' => $pago->id,
            'receptor_id' => $request->receptor_id,
            'monto' => $montoFloat,
        ]);

        $mensajeExito = $pago->tieneComprobante()
            ? 'Pago y comprobante registrados correctamente. Se aplicará a los saldos una vez que el receptor lo confirme.'
            : 'Pago registrado. Se aplicará a los saldos una vez que el receptor lo confirme.';

        return redirect()->route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos'])
            ->with('exito', $mensajeExito);
    }

    /**
     * Confirmar un pago pendiente (solo receptor/acreedor).
     */
    public function confirmarPago(Request $request, Pago $pago)
    {
        Gate::authorize('confirmar', $pago);

        if ($pago->estado === 'confirmado') {
            return back()->with('error', 'El pago ya se encuentra confirmado.');
        }

        $pago->update([
            'estado' => 'confirmado',
            'fecha_resolucion' => now(),
        ]);

        $nombreDeudor = $pago->deudor?->name ?? 'deudor';

        // Trazabilidad
        $pago->plan->actividades()->create([
            'user_id' => auth()->id(),
            'destinatario_id' => $pago->deudor_id,
            'accion' => 'pago_confirmado',
            'descripcion' => auth()->user()->name." confirmó el pago de Bs {$pago->monto} recibido de {$nombreDeudor}.",
            'estado_resultante' => $pago->plan->estado,
        ]);

        Evento::registrar('pago_confirmado', [
            'plan_id' => $pago->plan_id,
            'pago_id' => $pago->id,
            'monto' => $pago->monto,
        ]);

        return redirect()->route('planes.detalle', ['plan' => $pago->plan_id, 'tab' => 'gastos'])
            ->with('exito', '¡Pago confirmado! Los saldos netos han sido actualizados.');
    }

    /**
     * Rechazar un pago pendiente con motivo obligatorio (solo receptor/acreedor).
     */
    public function rechazarPago(RechazarPagoRequest $request, Pago $pago)
    {
        Gate::authorize('rechazar', $pago);

        if ($pago->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden rechazar pagos que se encuentren en estado pendiente.');
        }

        $pago->update([
            'estado' => 'rechazado',
            'motivo_rechazo' => $request->motivo_rechazo,
            'fecha_resolucion' => now(),
        ]);

        $nombreDeudor = $pago->deudor?->name ?? 'deudor';

        // Trazabilidad
        $pago->plan->actividades()->create([
            'user_id' => auth()->id(),
            'destinatario_id' => $pago->deudor_id,
            'accion' => 'pago_rechazado',
            'descripcion' => auth()->user()->name." rechazó el pago de Bs {$pago->monto} de {$nombreDeudor}. Motivo: {$request->motivo_rechazo}",
            'estado_resultante' => $pago->plan->estado,
        ]);

        Evento::registrar('pago_rechazado', [
            'plan_id' => $pago->plan_id,
            'pago_id' => $pago->id,
            'monto' => $pago->monto,
            'motivo' => $request->motivo_rechazo,
        ]);

        return redirect()->route('planes.detalle', ['plan' => $pago->plan_id, 'tab' => 'gastos'])
            ->with('exito', 'El pago ha sido rechazado. La deuda original se mantiene intacta.');
    }
}
