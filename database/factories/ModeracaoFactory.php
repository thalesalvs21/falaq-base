<?php

namespace Database\Factories;

use App\Models\Moderacao;
use App\Models\Pergunta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Moderacao>
 */
class ModeracaoFactory extends Factory
{
    protected $model = Moderacao::class;

    public function definition(): array
    {
        return [
            'pergunta_id' => Pergunta::factory(),
            'motivo' => fake()->sentence(12),
            'status' => 'analisando',
        ];
    }
}
