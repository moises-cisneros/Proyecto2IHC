<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'planes';

    protected $fillable = ['nombre', 'organizador', 'fecha_limite', 'estado', 'user_id'];

    protected $casts = [
        'fecha_limite' => 'date',
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
