@extends('layouts.app')

@section('title', $evento->titulo . ' — FalaQ')

@section('content')
<div class="row">
    <!-- Formularço de envio de Pergunta -->
    <div class="col-md-5 mb-4">
        @if(session('permissao'))
            <div class="text-red-500 fw-bold">
                {{ session('permissao') }}
            </div>
        @endif
        @can('delete', $evento)
            <form action="{{ route('eventos.destroy', $evento)  }}" method="post">
                @csrf
                @method('DELETE')
                <button type="submit">Deletar Evento</button>
            </form>
        @endcan

        @can('moderate', $evento)
            <a href="{{ route('moderacoes.index', $evento) }}" class="btn btn-outline-warning btn-sm mb-3">
                🛡️ Fila de Moderação
            </a>
        @endcan

        <div class="card shadow-sm p-3">
            <h4 class="fw-bold mb-3">💬 Faça sua Pergunta</h4>
            <form action="{{ route('eventos.perguntas.store', $evento->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="texto" class="form-label text-secondary">Texto da Pergunta</label>

                    <textarea name="texto" id="texto" rows="4" 
                              class="form-control bg-dark text-white border-secondary @error('texto') is-invalid @enderror"
                              placeholder="Digite sua dúvida ou comentário para o palestrante..."></textarea>

                    @error('texto')
                        <div class="invalid-feedback fw-bold">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-bold">Enviar Pergunta</button>
            </form>
        </div>
    </div>

    <!-- Lista de Perguntas (TICKET #002) -->
    <div class="col-md-7">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold m-0">📋 Perguntas do Evento <span class="badge bg-primary fs-6">🏆 Mais votadas no topo</span></h4>
            <span class="text-secondary small">Total no Banco: {{ $evento->perguntas->count() }}</span>
        </div>

        @forelse($perguntas as $pergunta)
            @php($jaVotou = ($pergunta->votos_do_usuario_count ?? 0) > 0)
            <div class="card mb-3 shadow-sm border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex gap-3">
                        <!-- TICKET #012: botao de upvote e contador de votos -->
                        <div class="text-center" style="min-width: 76px;">
                            @auth
                                <form action="{{ route('perguntas.votar', $pergunta) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-sm w-100 fw-bold {{ $jaVotou ? 'btn-primary' : 'btn-outline-secondary' }}"
                                            title="{{ $jaVotou ? 'Clique para remover seu voto' : 'Clique para votar nesta pergunta' }}">
                                        ▲
                                        <span class="d-block fs-5">{{ $pergunta->votos_count }}</span>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login.create') }}"
                                   class="btn btn-sm btn-outline-secondary w-100 fw-bold"
                                   title="Entre para votar nesta pergunta">
                                    ▲
                                    <span class="d-block fs-5">{{ $pergunta->votos_count }}</span>
                                </a>
                            @endauth
                            <span class="text-secondary" style="font-size: .7rem;">
                                {{ $pergunta->votos_count == 1 ? 'voto' : 'votos' }}
                            </span>
                        </div>

                        <div class="flex-grow-1">
                            <p class="fs-5 mb-2 text-white">{{ $pergunta->texto }}</p>
                            <div class="d-flex justify-content-between align-items-center text-secondary small">
                                <span>
                                    👤 <strong>{{ $pergunta->user->name ?? 'Anônimo' }}</strong>
                                    &nbsp;·&nbsp;
                                    Status: <span class="badge bg-success">{{ $pergunta->status }}</span>
                                    @if($jaVotou)
                                        &nbsp;·&nbsp;
                                        <span class="badge bg-primary">Você votou</span>
                                    @endif
                                </span>
                                <span>{{ $pergunta->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            @can('delete', $pergunta)
                                <form action="{{ route('perguntas.destroy', $pergunta) }}" method="post" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <x-danger-button>Excluir Pergunta</x-danger-button>
                                </form>
                            @endcan

                            <!-- TICKET #016: denúncia da pergunta -->
                            @auth
                                @php($erroDenuncia = $errors->has('motivo') && (int) old('pergunta_id') === $pergunta->id)
                                <button class="btn btn-link btn-sm text-secondary p-0 mt-2" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#denuncia-{{ $pergunta->id }}">
                                    ⚠️ Reportar pergunta
                                </button>

                                <div class="collapse {{ $erroDenuncia ? 'show' : '' }}" id="denuncia-{{ $pergunta->id }}">
                                    <form action="{{ route('perguntas.moderar', $pergunta) }}" method="POST" class="mt-2">
                                        @csrf
                                        <input type="hidden" name="pergunta_id" value="{{ $pergunta->id }}">
                                        <textarea name="motivo" rows="2"
                                                  class="form-control form-control-sm bg-dark text-white border-secondary {{ $erroDenuncia ? 'is-invalid' : '' }}"
                                                  placeholder="Por que esta pergunta deve ser moderada? (10 a 255 caracteres)">{{ $erroDenuncia ? old('motivo') : '' }}</textarea>
                                        @if($erroDenuncia)
                                            <div class="invalid-feedback fw-bold">{{ $errors->first('motivo') }}</div>
                                        @endif
                                        <button type="submit" class="btn btn-warning btn-sm fw-bold mt-2">Enviar denúncia</button>
                                    </form>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-dark text-center p-4">
                Nenhuma pergunta enviada ainda. Seja o primeiro!
            </div>
        @endforelse

        <!-- TICKET #002: Renderização dos Botões de Paginação -->
        @if(method_exists($perguntas, 'links'))
            <div class="d-flex justify-content-center mt-4">
                
            </div>
        @endif
    </div>
</div>
@endsection
