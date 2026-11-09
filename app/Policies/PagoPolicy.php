<?php

namespace App\Policies;

use App\Models\Pago;
use App\Models\Plan;
use App\Models\User;

class PagoPolicy
{
    /**
     * Determina si el usuario puede registrar un pago en el plan.
     */
    public function create(User $user, Plan $plan): bool
    {
        if ($plan->estado === 'cancelado') {
            return false;
        }

        return $this->esParticipanteDelPlan($user, $plan);
    }

    /**
     * Determina si el usuario puede confirmar un pago (solo el receptor acreedor).
     */
    public function confirmar(User $user, Pago $pago): bool
    {
        if ((int) $user->id !== (int) $pago->receptor_id) {
            return false;
        }

        return $pago->estado === 'pendiente';
    }

    /**
     * Determina si el usuario puede rechazar un pago (solo el receptor acreedor).
     */
    public function rechazar(User $user, Pago $pago): bool
    {
        if ((int) $user->id !== (int) $pago->receptor_id) {
            return false;
        }

        return $pago->estado === 'pendiente';
    }

    /**
     * Verifica si el usuario es creador o participante aceptado del plan.
     */
    protected function esParticipanteDelPlan(User $user, Plan $plan): bool
    {
        if ((int) $user->id === (int) $plan->user_id) {
            return true;
        }

        return $plan->participantes()->where('users.id', $user->id)->exists()
            || $plan->invitaciones()
                ->where('estado', 'aceptada')
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhere('email', $user->email);
                })->exists();
    }
}
