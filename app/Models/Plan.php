<?php

namespace App\Models;

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
        if (!$this->validarPropuestaPertenece($opcion, $opcion->tipo)) {
            throw new \InvalidArgumentException("La propuesta #{$opcion->id} no pertenece al plan #{$this->id}.");
        }

        if ($this->esInmutable()) {
            throw new \DomainException("No se pueden asignar opciones a un plan en estado {$this->estado}.");
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
