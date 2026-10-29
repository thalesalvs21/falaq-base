<?php

namespace App\Policies;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EventoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Evento $evento): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Evento $evento): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Evento $evento): bool
    {
        return $user->id === $evento->user_id;
    }

    /**
     * Determina se o usuario pode moderar as perguntas do evento.
     * Somente o dono/organizador do evento (TICKET #016).
     */
    public function moderate(User $user, Evento $evento): bool
    {
        return $user->id === $evento->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Evento $evento): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Evento $evento): bool
    {
        return false;
    }
}
