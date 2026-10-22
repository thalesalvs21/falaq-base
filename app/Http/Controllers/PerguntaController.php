<?php

namespace App\Http\Controllers;

use App\Models\Pergunta;
use Illuminate\Support\Facades\Auth;

class PerguntaController extends Controller
{
    /**
     * Alterna o upvote do usuario autenticado na pergunta (TICKET #011).
     */
    public function votar(Pergunta $pergunta)
    {
        $pergunta->votos()->toggle(Auth::id());

        $votou = $pergunta->votos()->whereKey(Auth::id())->exists();

        return back()->with('sucesso', $votou
            ? 'Voto registrado! Essa pergunta subiu no ranking.'
            : 'Voto removido dessa pergunta.');
    }
}
