<?php

namespace App\Models;

use Database\Factories\PlanActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanActivity extends Model
{
    /** @use HasFactory<PlanActivityFactory> */
    use HasFactory;

    protected $table = 'plan_activities';

    protected $fillable = [
        'plan_id',
        'user_id',
        'destinatario_id',
        'accion',
        'descripcion',
        'estado_resultante',
        'created_at',
        'updated_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Plan asociado a la actividad.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Usuario que origino o realizo la accion.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Usuario destinatario o contraparte de la accion.
     */
    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destinatario_id');
    }

    /**
     * Scope para filtrar actividades donde el usuario es autor o destinatario bilateral.
     */
    public function scopeVisiblesParaUsuario($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere('destinatario_id', $userId);
        });
    }

    /**
     * Scope para ordenar cronologicamente de forma descendente.
     */
    public function scopeRecientes($query)
    {
        return $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }
}
