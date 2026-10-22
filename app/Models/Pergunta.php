<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pergunta extends Model
{
    use HasFactory;

    protected $fillable = ['evento_id', 'texto', 'status', 'user_id', 'is_public'];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Usuarios que deram upvote nesta pergunta (TICKET #010).
     *
     * @return BelongsToMany<User>
     */
    public function votos(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'pergunta_user')
            ->withTimestamps();
    }

    /**
     * Indica se o usuario informado ja votou nesta pergunta.
     */
    public function votadoPor(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->relationLoaded('votos')) {
            return $this->votos->contains($user->getKey());
        }

        return $this->votos()->whereKey($user->getKey())->exists();
    }
}
