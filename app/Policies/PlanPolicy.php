<?php

namespace App\Policies;

use App\Models\Plan;
use App\Models\User;

class PlanPolicy
{
    /**
     * Determine whether the user can view the plan.
     */
    public function view(User $user, Plan $plan): bool
    {
        return $this->isCreatorOrParticipante($user, $plan) || $this->isInvitado($user, $plan);
    }

    /**
     * Determine whether the user can accept an invitation to the plan.
     */
    public function aceptar(User $user, Plan $plan): bool
    {
        return $this->isInvitado($user, $plan);
    }

    /**
     * Determine whether the user can update the plan.
     */
    public function update(User $user, Plan $plan): bool
    {
        return $this->isCreator($user, $plan);
    }

    /**
     * Determine whether the user can delete the plan.
     */
    public function delete(User $user, Plan $plan): bool
    {
        return $this->isCreator($user, $plan);
    }

    /**
     * Determine whether the user can change the status or cancel the plan.
     */
    public function cambiarEstado(User $user, Plan $plan): bool
    {
        return $this->isCreator($user, $plan);
    }

    /**
     * Determine whether the user can invite collaborators to the plan.
     */
    public function invitar(User $user, Plan $plan): bool
    {
        return $this->isCreator($user, $plan);
    }

    /**
     * Determine whether the user can propose date or location options.
     */
    public function proponer(User $user, Plan $plan): bool
    {
        return $this->isCreatorOrParticipante($user, $plan);
    }

    /**
     * Determine whether the user can vote on plan options.
     */
    public function votar(User $user, Plan $plan): bool
    {
        return $this->isCreatorOrParticipante($user, $plan);
    }

    /**
     * Determine whether the user can abandon the plan.
     * Only accepted participants can abandon. The creator cannot abandon their own plan.
     */
    public function abandonar(User $user, Plan $plan): bool
    {
        if ($this->isCreator($user, $plan)) {
            return false;
        }

        return $this->isParticipante($user, $plan);
    }

    /**
     * Determine whether the user is the plan creator/organizer.
     */
    public function isCreator(User $user, Plan $plan): bool
    {
        return (int) $user->id === (int) $plan->user_id;
    }

    /**
     * Determine whether the user is an accepted participant.
     */
    public function isParticipante(User $user, Plan $plan): bool
    {
        return $plan->participantes()->where('users.id', $user->id)->exists()
            || $plan->invitaciones()
                    ->where('estado', 'aceptada')
                    ->where(function ($query) use ($user) {
                        $query->where('user_id', $user->id)
                              ->orWhere('email', $user->email);
                    })->exists();
    }

    /**
     * Determine whether the user has a pending invitation to the plan.
     */
    public function isInvitado(User $user, Plan $plan): bool
    {
        return $plan->invitaciones()
                    ->where('estado', 'pendiente')
                    ->where(function ($query) use ($user) {
                        $query->where('user_id', $user->id)
                              ->orWhere('email', $user->email);
                    })->exists();
    }

    /**
     * Determine whether the user is the creator or an accepted participant.
     */
    public function isCreatorOrParticipante(User $user, Plan $plan): bool
    {
        return $this->isCreator($user, $plan) || $this->isParticipante($user, $plan);
    }
}
