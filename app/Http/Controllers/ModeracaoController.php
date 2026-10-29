<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreModeracaoRequest;
use App\Models\Evento;
use App\Models\Moderacao;
use App\Models\Pergunta;
use Illuminate\Http\Request;

class ModeracaoController extends Controller
{
    /**
     * Registra a denuncia de uma pergunta (TICKET #016).
     * Qualquer participante autenticado pode reportar.
     */
    public function store(StoreModeracaoRequest $request, Pergunta $pergunta)
    {
        $pergunta->moderacoes()->create([
            'motivo' => $request->validated('motivo'),
            'status' => 'analisando',
        ]);

        return back()->with('sucesso', 'Denuncia registrada! A organizacao vai analisar essa pergunta.');
    }

    /**
     * Fila de moderacao do evento (TICKET #016).
     * Apenas o dono/moderador do evento, com eager loading para evitar N+1.
     */
    public function index(Evento $evento)
    {
        $this->authorize('moderate', $evento);

        $moderacoes = Moderacao::with('pergunta')
            ->whereHas('pergunta', function ($query) use ($evento) {
                $query->where('evento_id', $evento->id);
            })
            ->latest()
            ->paginate(15);

        return view('moderacoes.index', compact('evento', 'moderacoes'));
    }

    /**
     * Aprova ou rejeita uma denuncia (TICKET #016).
     * Somente o dono do evento decide o destino do caso.
     */
    public function updateStatus(Request $request, Moderacao $moderacao)
    {
        $this->authorize('moderate', $moderacao->pergunta->evento);

        $dados = $request->validate([
            'status' => ['required', 'in:aprovado,rejeitado,analisando'],
        ]);

        $moderacao->update(['status' => $dados['status']]);

        return back()->with('sucesso', "Denuncia marcada como {$dados['status']}.");
    }
}
