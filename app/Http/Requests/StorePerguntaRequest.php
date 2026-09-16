<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePerguntaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'evento_id' => $this->route('id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'texto'     => ['required', 'string', 'min:10', 'max:255'],
            'evento_id' => ['required', 'exists:eventos,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'texto.required'   => 'Escreva sua pergunta.',
            'texto.min'        => 'A pergunta precisa ter pelo menos 10 caracteres.',
            'texto.max'        => 'A pergunta pode ter no máximo 255 caracteres.',
            'evento_id.exists' => 'Este evento não existe.',
        ];
    }
}