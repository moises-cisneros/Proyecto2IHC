<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'planes';

    protected $fillable = [
        'nombre',
        'organizador',
        'fecha_limite',
        'estado',
        'user_id',
        'propuesta_fecha_id',
        'propuesta_lugar_id',
        'motivo_desempate',
        'fecha_confirmacion',
    ];

    protected $casts = [
        'fecha_limite' => 'date',
        'fecha_confirmacion' => 'datetime',
    ];

    /**
     * Ciclo de vida del modelo para auditoria de historial.
     */
    protected static function booted(): void
    {
        static::created(function (Plan $plan) {
            $userId = auth()->id() ?? $plan->user_id;
            $plan->actividades()->create([
                'user_id' => $userId,
                'accion' => 'creacion',
                'descripcion' => "Plan '{$plan->nombre}' creado.",
                'estado_resultante' => $plan->estado ?? 'pendiente',
            ]);
        });

        static::updated(function (Plan $plan) {
            $userId = auth()->id() ?? $plan->user_id;
            if ($plan->wasChanged('estado')) {
                $plan->actividades()->create([
                    'user_id' => $userId,
                    'accion' => 'cambio_estado',
                    'descripcion' => "Estado cambiado a {$plan->estado}.",
                    'estado_resultante' => $plan->estado,
                ]);

                if ($plan->estado === 'confirmado') {
                    Evento::registrar('plan_confirmado', [
                        'con_desempate' => ! empty($plan->motivo_desempate),
                        'tiene_propuesta_fecha' => ! empty($plan->propuesta_fecha_id),
                        'tiene_propuesta_lugar' => ! empty($plan->propuesta_lugar_id),
                        'tiene_fecha_limite' => ! empty($plan->fecha_limite),
                    ]);
                }
            } else {
                $plan->actividades()->create([
                    'user_id' => $userId,
                    'accion' => 'actualizacion',
                    'descripcion' => "Plan '{$plan->nombre}' actualizado.",
                    'estado_resultante' => $plan->estado,
                ]);
            }
        });
    }

    /**
     * Relacion con actividades e historial.
     */
    public function actividades(): HasMany
    {
        return $this->hasMany(PlanActivity::class);
    }

    /**
     * Usuario creador/organizador del plan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Propuesta de fecha ganadora confirmada para el plan.
     */
    public function propuestaFechaGanadora(): BelongsTo
    {
        return $this->belongsTo(Opcion::class, 'propuesta_fecha_id');
    }

    /**
     * Propuesta de lugar ganadora confirmada para el plan.
     */
    public function propuestaLugarGanadora(): BelongsTo
    {
        return $this->belongsTo(Opcion::class, 'propuesta_lugar_id');
    }

    /**
     * Evalua si existe empate en el conteo maximo de votos para un tipo de propuesta.
     */
    public function tieneEmpateTipo(string $tipo): bool
    {
        $opciones = $this->opciones()->where('tipo', $tipo)->withCount('votos')->get();
        if ($opciones->count() < 2) {
            return false;
        }

        $maxVotos = $opciones->max('votos_count');
        $candidatasMax = $opciones->where('votos_count', $maxVotos);

        return $candidatasMax->count() > 1;
    }

    /**
     * Evalua si hay empate entre propuestas de fecha mas votadas.
     */
    public function tieneEmpateFechas(): bool
    {
        return $this->tieneEmpateTipo('fecha');
    }

    /**
     * Evalua si hay empate entre propuestas de lugar mas votadas.
     */
    public function tieneEmpateLugares(): bool
    {
        return $this->tieneEmpateTipo('lugar');
    }

    /**
     * Obtiene las propuestas empatadas en el maximo de votos.
     */
    public function obtenerPropuestasEmpatadas(string $tipo)
    {
        $opciones = $this->opciones()->where('tipo', $tipo)->withCount('votos')->get();
        if ($opciones->isEmpty()) {
            return collect();
        }

        $maxVotos = $opciones->max('votos_count');

        return $opciones->where('votos_count', $maxVotos)->values();
    }

    /**
     * Obtiene el ganador indiscutido si existe una unica opcion con la mayoria estricta.
     */
    public function obtenerGanadorIndiscutido(string $tipo): ?Opcion
    {
        $opciones = $this->opciones()->where('tipo', $tipo)->withCount('votos')->get();
        if ($opciones->isEmpty()) {
            return null;
        }

        $maxVotos = $opciones->max('votos_count');
        $candidatas = $opciones->where('votos_count', $maxVotos);

        if ($candidatas->count() === 1) {
            return $candidatas->first();
        }

        return null;
    }

    /**
     * Verifica si el plan se encuentra en un estado inmutable (confirmado o cancelado).
     */
    public function esInmutable(): bool
    {
        return in_array($this->estado, ['confirmado', 'cancelado'], true);
    }

    /**
     * Valida si una propuesta pertenece a este plan y coincide con el tipo esperado.
     */
    public function validarPropuestaPertenece(Opcion $opcion, string $tipo): bool
    {
        return $opcion->plan_id === $this->id && $opcion->tipo === $tipo;
    }

    /**
     * Asigna una propuesta ganadora y registra el motivo si aplica, protegiendo la integridad.
     */
    public function asignarGanadora(Opcion $opcion, ?string $motivo = null): void
    {
        if (! $this->validarPropuestaPertenece($opcion, $opcion->tipo)) {
            throw new \InvalidArgumentException("La propuesta #{$opcion->id} no pertenece al plan #{$this->id}.");
        }

        if ($this->esInmutable()) {
            throw new DomainException("No se pueden asignar opciones a un plan en estado {$this->estado}.");
        }

        if ($opcion->tipo === 'fecha') {
            $this->propuesta_fecha_id = $opcion->id;
        } elseif ($opcion->tipo === 'lugar') {
            $this->propuesta_lugar_id = $opcion->id;
        }

        if ($motivo !== null) {
            $this->motivo_desempate = $motivo;
        }
    }

    /**
     * Opciones de fecha o lugar propuestas para el plan.
     */
    public function opciones(): HasMany
    {
        return $this->hasMany(Opcion::class);
    }

    /**
     * Invitaciones emitidas para el plan.
     */
    public function invitaciones(): HasMany
    {
        return $this->hasMany(Invitacion::class);
    }

    /**
     * Participantes que han aceptado la invitación al plan.
     */
    public function participantes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'invitaciones', 'plan_id', 'user_id')
            ->wherePivot('estado', 'aceptada');
    }

    /**
     * Gastos registrados en el plan.
     */
    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class);
    }

    /**
     * Pagos registrados entre participantes del plan.
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    /**
     * Calcula los saldos netos consolidados y las deudas bilaterales entre participantes.
     *
     * @return array{
     *     total_gastado: float,
     *     balances: array<int, array{user: User, pagado: float, consumido: float, neto: float}>,
     *     deudas: array<int, array{deudor_id: int, deudor: User, acreedor_id: int, acreedor: User, monto: float}>
     * }
     */
    public function calcularSaldosNetos(): array
    {
        $gastos = $this->gastos()->with(['pagador', 'divisiones.user'])->get();
        $pagosConfirmados = $this->pagos()->where('estado', 'confirmado')->with(['deudor', 'receptor'])->get();

        // Obtener todos los usuarios participantes o involucrados
        $usuarios = collect([$this->user])
            ->merge($this->participantes)
            ->filter()
            ->keyBy('id');

        foreach ($gastos as $gasto) {
            if ($gasto->pagador) {
                $usuarios->put($gasto->pagador->id, $gasto->pagador);
            }
            foreach ($gasto->divisiones as $div) {
                if ($div->user) {
                    $usuarios->put($div->user->id, $div->user);
                }
            }
        }

        foreach ($pagosConfirmados as $pago) {
            if ($pago->deudor) {
                $usuarios->put($pago->deudor->id, $pago->deudor);
            }
            if ($pago->receptor) {
                $usuarios->put($pago->receptor->id, $pago->receptor);
            }
        }

        $matriz = [];
        $totalGastado = 0.0;
        $balances = [];

        foreach ($usuarios as $id => $user) {
            $balances[$id] = [
                'user' => $user,
                'pagado' => 0.0,
                'consumido' => 0.0,
                'pagos_hechos' => 0.0,
                'pagos_recibidos' => 0.0,
                'neto' => 0.0,
            ];
        }

        // 1. Procesar gastos
        foreach ($gastos as $gasto) {
            $pagadorId = (int) $gasto->pagador_id;
            $montoGasto = (float) $gasto->monto_total;
            $totalGastado += $montoGasto;

            if (isset($balances[$pagadorId])) {
                $balances[$pagadorId]['pagado'] += $montoGasto;
            }

            foreach ($gasto->divisiones as $division) {
                $consumidorId = (int) $division->user_id;
                $montoAsignado = (float) $division->monto_asignado;

                if (isset($balances[$consumidorId])) {
                    $balances[$consumidorId]['consumido'] += $montoAsignado;
                }

                if ($consumidorId !== $pagadorId && $montoAsignado > 0) {
                    $matriz[$consumidorId][$pagadorId] = ($matriz[$consumidorId][$pagadorId] ?? 0.0) + $montoAsignado;
                }
            }
        }

        // 2. Procesar pagos confirmados
        foreach ($pagosConfirmados as $pago) {
            $deudorId = (int) $pago->deudor_id;
            $receptorId = (int) $pago->receptor_id;
            $montoPago = (float) $pago->monto;

            if (isset($balances[$deudorId])) {
                $balances[$deudorId]['pagos_hechos'] += $montoPago;
            }
            if (isset($balances[$receptorId])) {
                $balances[$receptorId]['pagos_recibidos'] += $montoPago;
            }

            $matriz[$deudorId][$receptorId] = ($matriz[$deudorId][$receptorId] ?? 0.0) - $montoPago;
        }

        // 3. Consolidar balances
        foreach ($balances as $id => &$b) {
            $b['neto'] = round($b['pagado'] + $b['pagos_hechos'] - $b['consumido'] - $b['pagos_recibidos'], 2);
            $b['pagado'] = round($b['pagado'], 2);
            $b['consumido'] = round($b['consumido'], 2);
        }
        unset($b);

        // 4. Calcular deudas bilaterales netas
        $userIds = $usuarios->keys()->all();
        $deudas = [];

        for ($i = 0; $i < count($userIds); $i++) {
            for ($j = $i + 1; $j < count($userIds); $j++) {
                $u1 = $userIds[$i];
                $u2 = $userIds[$j];

                $deuda1a2 = $matriz[$u1][$u2] ?? 0.0;
                $deuda2a1 = $matriz[$u2][$u1] ?? 0.0;

                $neto = round($deuda1a2 - $deuda2a1, 2);

                if ($neto > 0.009) {
                    $deudas[] = [
                        'deudor_id' => $u1,
                        'deudor' => $usuarios->get($u1),
                        'acreedor_id' => $u2,
                        'acreedor' => $usuarios->get($u2),
                        'monto' => $neto,
                    ];
                } elseif ($neto < -0.009) {
                    $deudas[] = [
                        'deudor_id' => $u2,
                        'deudor' => $usuarios->get($u2),
                        'acreedor_id' => $u1,
                        'acreedor' => $usuarios->get($u1),
                        'monto' => abs($neto),
                    ];
                }
            }
        }

        return [
            'total_gastado' => round($totalGastado, 2),
            'balances' => $balances,
            'deudas' => $deudas,
        ];
    }

    /**
     * Determina si un usuario posee saldos deudores pendientes en los gastos del plan.
     */
    public function usuarioTieneDeuda(int $userId): bool
    {
        $saldos = $this->calcularSaldosNetos();
        foreach ($saldos['deudas'] as $deuda) {
            if ((int) $deuda['deudor_id'] === $userId && (float) $deuda['monto'] > 0.009) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtiene el monto total de deuda acumulada que un usuario debe saldar en el plan.
     */
    public function obtenerMontoDeudaUsuario(int $userId): float
    {
        $saldos = $this->calcularSaldosNetos();
        $total = 0.0;
        foreach ($saldos['deudas'] as $deuda) {
            if ((int) $deuda['deudor_id'] === $userId && (float) $deuda['monto'] > 0.009) {
                $total += (float) $deuda['monto'];
            }
        }

        return round($total, 2);
    }

    /**
     * Retira formalmente a un participante del plan, anulando sus votos emitidos,
     * desvinculando su membresía y registrando la actividad en la bitácora.
     *
     * @throws DomainException
     */
    public function retirarParticipante(User $user): void
    {
        if ((int) $this->user_id === (int) $user->id) {
            throw new DomainException('El organizador no puede retirarse del plan.');
        }

        if ($this->usuarioTieneDeuda($user->id)) {
            throw new DomainException('No puedes retirarte mientras tengas deudas pendientes.');
        }

        // 1. Eliminar físicamente los registros de votos emitidos por el usuario en opciones del plan
        $opcionIds = $this->opciones()->pluck('id');
        Voto::whereIn('opcion_id', $opcionIds)
            ->where('user_id', $user->id)
            ->delete();

        // 2. Desvincular de la relación de participantes (invitaciones)
        $this->invitaciones()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('email', $user->email);
            })
            ->delete();

        // 3. Registrar actividad formal en el historial del plan
        $this->actividades()->create([
            'user_id' => $user->id,
            'accion' => 'participante_retirado',
            'descripcion' => "{$user->name} se retiró del plan.",
            'estado_resultante' => $this->estado,
        ]);
    }

    //  para busqueda y filtros
    public function scopeFiltrar($query, ?string $buscar, ?string $estado, ?string $fecha)
    {
        if ($buscar) {
            $query->where('nombre', 'like', "%{$buscar}%");
        }

        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($fecha === '7dias') {
            $query->whereBetween('fecha_limite', [now()->startOfDay(), now()->addDays(7)->endOfDay()]);
        } elseif ($fecha === 'vencidos') {
            $query->where('fecha_limite', '<', now()->startOfDay());
        }

        return $query;
    }
}
