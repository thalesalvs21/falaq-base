<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UpvotePerguntaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_tabela_pivot_possui_colunas_esperadas(): void
    {
        $this->assertTrue(Schema::hasTable('pergunta_user'));
        $this->assertTrue(Schema::hasColumns('pergunta_user', [
            'pergunta_id', 'user_id', 'created_at', 'updated_at',
        ]));
    }

    public function test_pivot_impede_voto_duplicado_do_mesmo_usuario(): void
    {
        $pergunta = Pergunta::factory()->create();
        $user = User::factory()->create();

        $pergunta->votos()->attach($user->id);

        $this->expectException(QueryException::class);

        $pergunta->votos()->attach($user->id);
    }

    public function test_relacionamentos_belongs_to_many_estao_configurados(): void
    {
        $pergunta = Pergunta::factory()->create();
        $user = User::factory()->create();

        $this->assertInstanceOf(BelongsToMany::class, $pergunta->votos());
        $this->assertInstanceOf(BelongsToMany::class, $user->perguntasVotadas());

        $pergunta->votos()->attach($user->id);

        $this->assertTrue($pergunta->votos->contains($user));
        $this->assertTrue($user->perguntasVotadas->contains($pergunta));
    }

    public function test_usuario_anonimo_nao_pode_votar(): void
    {
        $pergunta = Pergunta::factory()->create();

        $this->post(route('perguntas.votar', $pergunta))
            ->assertRedirect(route('login.create'));

        $this->assertDatabaseCount('pergunta_user', 0);
    }

    public function test_usuario_autenticado_registra_voto(): void
    {
        $pergunta = Pergunta::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('eventos.show', $pergunta->evento_id))
            ->post(route('perguntas.votar', $pergunta))
            ->assertRedirect(route('eventos.show', $pergunta->evento_id));

        $this->assertDatabaseHas('pergunta_user', [
            'pergunta_id' => $pergunta->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_voto_repetido_remove_registro_via_toggle(): void
    {
        $pergunta = Pergunta::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('perguntas.votar', $pergunta));
        $this->actingAs($user)->post(route('perguntas.votar', $pergunta));

        $this->assertDatabaseMissing('pergunta_user', [
            'pergunta_id' => $pergunta->id,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseCount('pergunta_user', 0);
    }

    public function test_perguntas_do_evento_sao_ordenadas_por_numero_de_votos(): void
    {
        $evento = Evento::factory()->create();

        $semVotos = Pergunta::factory()->create([
            'evento_id' => $evento->id,
            'texto' => 'Pergunta sem nenhum voto da plateia',
        ]);
        $maisVotada = Pergunta::factory()->create([
            'evento_id' => $evento->id,
            'texto' => 'Pergunta campea de votos da plateia',
        ]);
        $umVoto = Pergunta::factory()->create([
            'evento_id' => $evento->id,
            'texto' => 'Pergunta com um unico voto da plateia',
        ]);

        $maisVotada->votos()->attach(User::factory()->count(3)->create()->pluck('id'));
        $umVoto->votos()->attach(User::factory()->create()->id);

        $response = $this->get(route('eventos.show', $evento->id));

        $response->assertOk();

        $perguntas = $response->viewData('perguntas');

        $this->assertSame(
            [$maisVotada->id, $umVoto->id, $semVotos->id],
            $perguntas->pluck('id')->all()
        );
        $this->assertSame(3, $perguntas->first()->votos_count);
        $response->assertSeeInOrder([$maisVotada->texto, $umVoto->texto, $semVotos->texto]);
    }

    public function test_listagem_do_evento_nao_mistura_perguntas_de_outros_eventos(): void
    {
        $evento = Evento::factory()->create();
        $minha = Pergunta::factory()->create(['evento_id' => $evento->id]);
        $outra = Pergunta::factory()->create();

        $response = $this->get(route('eventos.show', $evento->id));

        $response->assertOk();
        $this->assertSame([$minha->id], $response->viewData('perguntas')->pluck('id')->all());
        $response->assertDontSee($outra->texto);
    }

    public function test_telao_exibe_contador_e_formulario_de_voto(): void
    {
        $evento = Evento::factory()->create();
        $pergunta = Pergunta::factory()->create(['evento_id' => $evento->id]);
        $user = User::factory()->create();

        $pergunta->votos()->attach($user->id);

        $this->actingAs($user)
            ->get(route('eventos.show', $evento->id))
            ->assertOk()
            ->assertSee(route('perguntas.votar', $pergunta))
            ->assertSee('Você votou');
    }
}
