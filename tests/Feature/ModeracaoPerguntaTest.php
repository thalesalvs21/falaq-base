<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Moderacao;
use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ModeracaoPerguntaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_tabela_moderacoes_tem_colunas_e_status_padrao(): void
    {
        $this->assertTrue(Schema::hasTable('moderacoes'));
        $this->assertTrue(Schema::hasColumns('moderacoes', [
            'pergunta_id', 'motivo', 'status', 'created_at', 'updated_at',
        ]));

        $pergunta = Pergunta::factory()->create();

        $id = DB::table('moderacoes')->insertGetId([
            'pergunta_id' => $pergunta->id,
            'motivo' => 'Conteudo ofensivo contra o palestrante',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('analisando', DB::table('moderacoes')->find($id)->status);
    }

    public function test_pergunta_e_apagada_em_cascata_remove_moderacoes(): void
    {
        $moderacao = Moderacao::factory()->create();

        $moderacao->pergunta->delete();

        $this->assertDatabaseCount('moderacoes', 0);
    }

    public function test_relacionamentos_1n_estao_configurados(): void
    {
        $moderacao = Moderacao::factory()->create();

        $this->assertInstanceOf(BelongsTo::class, $moderacao->pergunta());
        $this->assertInstanceOf(HasMany::class, $moderacao->pergunta->moderacoes());
        $this->assertTrue($moderacao->pergunta->moderacoes->contains($moderacao));
    }

    public function test_denuncia_exige_motivo_valido(): void
    {
        $pergunta = Pergunta::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('eventos.show', $pergunta->evento_id))
            ->post(route('perguntas.moderar', $pergunta), ['motivo' => 'curto'])
            ->assertSessionHasErrors('motivo');

        $this->actingAs($user)
            ->post(route('perguntas.moderar', $pergunta), ['motivo' => ''])
            ->assertSessionHasErrors('motivo');

        $this->actingAs($user)
            ->post(route('perguntas.moderar', $pergunta), ['motivo' => str_repeat('a', 256)])
            ->assertSessionHasErrors('motivo');

        $this->assertDatabaseCount('moderacoes', 0);
    }

    public function test_participante_autenticado_registra_denuncia(): void
    {
        $pergunta = Pergunta::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('eventos.show', $pergunta->evento_id))
            ->post(route('perguntas.moderar', $pergunta), [
                'motivo' => 'Pergunta ofensiva e fora do escopo da palestra',
            ])
            ->assertRedirect(route('eventos.show', $pergunta->evento_id));

        $this->assertDatabaseHas('moderacoes', [
            'pergunta_id' => $pergunta->id,
            'motivo' => 'Pergunta ofensiva e fora do escopo da palestra',
            'status' => 'analisando',
        ]);
    }

    public function test_anonimo_nao_pode_denunciar(): void
    {
        $pergunta = Pergunta::factory()->create();

        $this->post(route('perguntas.moderar', $pergunta), [
            'motivo' => 'Pergunta ofensiva e fora do escopo da palestra',
        ])->assertRedirect(route('login.create'));

        $this->assertDatabaseCount('moderacoes', 0);
    }

    public function test_fila_de_moderacao_so_e_vista_pelo_dono_do_evento(): void
    {
        $dono = User::factory()->create();
        $evento = Evento::factory()->create(['user_id' => $dono->id]);
        $pergunta = Pergunta::factory()->create(['evento_id' => $evento->id]);
        $moderacao = Moderacao::factory()->create(['pergunta_id' => $pergunta->id]);

        // O visitante anonimo e checado primeiro: actingAs vale para o
        // resto do teste e mascararia a verificacao de convidado.
        $this->get(route('moderacoes.index', $evento))
            ->assertRedirect(route('login.create'));

        $this->actingAs(User::factory()->create())
            ->get(route('moderacoes.index', $evento))
            ->assertForbidden();

        $this->actingAs($dono)
            ->get(route('moderacoes.index', $evento))
            ->assertOk()
            ->assertSee($moderacao->motivo);
    }

    public function test_fila_usa_eager_loading_e_lista_so_o_evento(): void
    {
        $dono = User::factory()->create();
        $evento = Evento::factory()->create(['user_id' => $dono->id]);

        $deste = Moderacao::factory()->count(3)->create([
            'pergunta_id' => Pergunta::factory()->create(['evento_id' => $evento->id])->id,
        ]);
        $deOutro = Moderacao::factory()->create();

        $response = $this->actingAs($dono)->get(route('moderacoes.index', $evento));

        $response->assertOk();

        $moderacoes = $response->viewData('moderacoes');

        $this->assertCount(3, $moderacoes);
        $this->assertTrue($moderacoes->first()->relationLoaded('pergunta'));
        $this->assertEqualsCanonicalizing(
            $deste->pluck('id')->all(),
            $moderacoes->pluck('id')->all()
        );
        $response->assertDontSee($deOutro->motivo);
    }

    public function test_apenas_o_dono_do_evento_decide_o_status(): void
    {
        $dono = User::factory()->create();
        $evento = Evento::factory()->create(['user_id' => $dono->id]);
        $pergunta = Pergunta::factory()->create(['evento_id' => $evento->id]);
        $moderacao = Moderacao::factory()->create(['pergunta_id' => $pergunta->id]);

        $this->actingAs(User::factory()->create())
            ->patch(route('moderacoes.status', $moderacao), ['status' => 'aprovado'])
            ->assertForbidden();

        $this->assertDatabaseHas('moderacoes', [
            'id' => $moderacao->id,
            'status' => 'analisando',
        ]);

        $this->actingAs($dono)
            ->from(route('moderacoes.index', $evento))
            ->patch(route('moderacoes.status', $moderacao), ['status' => 'rejeitado'])
            ->assertRedirect(route('moderacoes.index', $evento));

        $this->assertDatabaseHas('moderacoes', [
            'id' => $moderacao->id,
            'status' => 'rejeitado',
        ]);
    }

    public function test_status_invalido_e_rejeitado(): void
    {
        $dono = User::factory()->create();
        $evento = Evento::factory()->create(['user_id' => $dono->id]);
        $pergunta = Pergunta::factory()->create(['evento_id' => $evento->id]);
        $moderacao = Moderacao::factory()->create(['pergunta_id' => $pergunta->id]);

        $this->actingAs($dono)
            ->from(route('moderacoes.index', $evento))
            ->patch(route('moderacoes.status', $moderacao), ['status' => 'qualquer-coisa'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('moderacoes', [
            'id' => $moderacao->id,
            'status' => 'analisando',
        ]);
    }
}
