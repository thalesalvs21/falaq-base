<?php

namespace App\Policies;

use App\Models\Pergunta;
use App\Models\User;

class PerguntaPolicy
{
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pergunta $pergunta): bool
    {
        return $user->id === $pergunta->user_id || $user->id === $pergunta->evento->user_id;
    }
}
