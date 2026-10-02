@extends('layouts.app')

@section('title', $evento->titulo . ' — FalaQ')

@section('content')
<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm p-3">
            <h4 class="fw-bold mb-3"> Faça sua Pergunta</h4>
            <form action="{{ route('eventos.perguntas.store', $evento->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="texto" class="form-label text-secondary">Texto da Pergunta</label>

                    <textarea name="texto" id="texto" rows="4" 
                              class="form-control bg-dark text-white @error('texto') border-red-500 @enderror"
                              placeholder="Digite sua dúvida ou comentário para o palestrante...">{{ old('texto') }}</textarea>

                    @error('texto')
                        <p class="text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Enviar Pergunta</button>
            </form>
        </div>
    </div>

    <div class="col-md-7">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold m-0"> Perguntas do Evento</h4>
            <span class="text-secondary small">Total no Banco: {{ $evento->perguntas->count() }}</span>
        </div>

        @forelse($perguntas as $pergunta)
            <div class="card mb-4 p-4 rounded-2xl shadow-sm border-start border-4 border-primary">
                <div class="card-body">
                    <p class="fs-5 mb-2 text-white">{{ $pergunta->texto }}</p>
                    <div class="d-flex justify-content-between align-items-center text-secondary small">
                        <span>Status: <span class="badge bg-success">{{ $pergunta->status }}</span></span>
                        <span>{{ $pergunta->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-dark text-center p-4">
                Nenhuma pergunta enviada ainda. Seja o primeiro!
            </div>
        @endforelse

        @if(method_exists($perguntas, 'links'))
            <div class="d-flex justify-content-center mt-4">
                
            </div>
        @endif
    </div>
</div>
@endsection
