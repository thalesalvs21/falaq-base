<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Moderacao extends Model
{
    use HasFactory;

    /**
     * O plural automatico do Eloquent para "Moderacao" nao resulta em
     * "moderacoes", por isso a tabela e declarada explicitamente.
     */
    protected $table = 'moderacoes';

    protected $fillable = ['pergunta_id', 'motivo', 'status'];

    /**
     * Pergunta denunciada (TICKET #014).
     *
     * @return BelongsTo<Pergunta, Moderacao>
     */
    public function pergunta(): BelongsTo
    {
        return $this->belongsTo(Pergunta::class);
    }
}
