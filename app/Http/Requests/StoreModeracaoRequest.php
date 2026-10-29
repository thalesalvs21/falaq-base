<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreModeracaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validacao da denuncia (TICKET #015).
     */
    public function rules(): array
    {
        return [
            'motivo' => ['required', 'string', 'min:10', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo.required' => 'Descreva o motivo da denuncia.',
            'motivo.min' => 'O motivo precisa ter pelo menos 10 caracteres.',
            'motivo.max' => 'O motivo pode ter no maximo 255 caracteres.',
        ];
    }
}
