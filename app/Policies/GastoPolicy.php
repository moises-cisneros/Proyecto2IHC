<?php

namespace App\Policies;

use App\Models\Gasto;
use App\Models\Plan;
use App\Models\User;

class GastoPolicy
{
    /**
     * Determina si el usuario puede ver los gastos del plan.
     */
    public function view(User $user, Gasto $gasto): bool
    {
        return $this->esParticipanteDelPlan($user, $gasto->plan);
    }

    /**
     * Determina si el usuario puede registrar un nuevo gasto en el plan.
     */
    public function create(User $user, Plan $plan): bool
    {
        if ($plan->estado === 'cancelado') {
            return false;
        }

        return $this->esParticipanteDelPlan($user, $plan);
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
