@extends('layouts.app')

@section('title', 'Fila de Moderação — ' . $evento->titulo)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold m-0">🛡️ Fila de Moderação</h2>
        <p class="text-secondary m-0">{{ $evento->titulo }}</p>
    </div>
    <a href="{{ route('eventos.show', $evento->id) }}" class="btn btn-outline-secondary btn-sm">
        &larr; Voltar ao evento
    </a>
</div>

@forelse($moderacoes as $moderacao)
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge {{ $moderacao->status === 'aprovado' ? 'bg-success' : ($moderacao->status === 'rejeitado' ? 'bg-danger' : 'bg-warning text-dark') }}">
                    {{ $moderacao->status }}
                </span>
                <span class="text-secondary small">{{ $moderacao->created_at->format('d/m/Y H:i') }}</span>
            </div>

            <p class="text-secondary small mb-1">Pergunta denunciada:</p>
            <p class="fs-5 text-white mb-3">{{ $moderacao->pergunta->texto ?? 'Pergunta removida' }}</p>

            <p class="text-secondary small mb-1">Motivo da denúncia:</p>
            <p class="mb-3">{{ $moderacao->motivo }}</p>

            <div class="d-flex gap-2">
                <form action="{{ route('moderacoes.status', $moderacao) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="aprovado">
                    <button type="submit" class="btn btn-success btn-sm fw-bold">Aprovar pergunta</button>
                </form>

                <form action="{{ route('moderacoes.status', $moderacao) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="rejeitado">
                    <button type="submit" class="btn btn-danger btn-sm fw-bold">Rejeitar pergunta</button>
                </form>
            </div>
        </div>
    </div>
@empty
    <div class="alert alert-dark text-center p-4">
        Nenhuma denúncia registrada neste evento. Tudo tranquilo por aqui!
    </div>
@endforelse

<div class="d-flex justify-content-center mt-4">
    {{ $moderacoes->links() }}
</div>
@endsection
