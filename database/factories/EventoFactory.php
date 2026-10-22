<?php

namespace Database\Factories;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evento>
 */
class EventoFactory extends Factory
{
    protected $model = Evento::class;

    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(4),
            'descricao' => fake()->paragraph(),
            'data_evento' => now()->addDays(fake()->numberBetween(1, 30)),
            'user_id' => User::factory(),
        ];
    }
}
