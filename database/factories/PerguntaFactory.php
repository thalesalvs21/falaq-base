<?php

namespace Database\Factories;

use App\Models\Evento;
use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pergunta>
 */
class PerguntaFactory extends Factory
{
    protected $model = Pergunta::class;

    public function definition(): array
    {
        return [
            'evento_id' => Evento::factory(),
            'user_id' => User::factory(),
            'texto' => fake()->sentence(10),
            'status' => 'pendente',
            'is_public' => true,
        ];
    }
}
