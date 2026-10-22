<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventoFormRequest;
use App\Models\Evento;
use App\Models\Pergunta;
use App\Http\Requests\StorePerguntaRequest;
use Illuminate\Support\Facades\Auth;

class EventoController extends Controller
{
    public function index()
    {
        $eventos = Evento::all();
        return view('eventos.index', compact('eventos'));
    }

    public function show($id)
    {
        $evento = Evento::findOrFail($id);

        // TICKET #012: ranking por relevancia (mais votadas no topo).
        $perguntas = Pergunta::with('user')
            ->withCount('votos')
            ->withCount(['votos as votos_do_usuario_count' => function ($query) {
                $query->where('pergunta_user.user_id', Auth::id());
            }])
            ->where('evento_id', $evento->id)
            ->orderByDesc('votos_count')
            ->latest()
            ->get();

        return view('eventos.show', compact('evento', 'perguntas'));
    }

 
    public function storePergunta(StorePerguntaRequest $request, $id)
    {
        $evento = Evento::findOrFail($id);

        Pergunta::create([
            'evento_id' => $evento->id,
            'user_id' => Auth::user()->id,
            'texto'     => $request->input('texto'),
            'status'    => 'pendente',
        ]);

        return redirect()->route('eventos.show', $evento->id)
            ->with('sucesso', 'Sua pergunta foi enviada com sucesso!');
    }

    public function create(){
        return view('eventos.create');
    }

    public function store(EventoFormRequest $request){
        $evento = $request->user()->eventos()->create($request->validated());
        return redirect()->route('eventos.show', $evento->id);
    }

    public function destroy($id)
    {
        $evento = Evento::find($id);
        // if(Auth::id() === $evento->user_id){
        if(Auth::user()->cannot('delete', $evento)){
            return redirect()->route('eventos.show', $evento)->with('permissao', 'Usuário não pode deletar esse evento');
        }
        $evento->delete();
        return redirect()->route('eventos.index');
    }

    public function destroyPergunta(Pergunta $pergunta)
    {
        $this->authorize('delete', $pergunta);
        $pergunta->delete();
        return redirect()->route('eventos.show', $pergunta->evento_id);
    }
}
