<?php

namespace Tests\Feature;

use App\Models\Configuracao;
use App\Models\PoliticaPrivacidade;
use App\Models\User;
use Database\Seeders\CardapioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PoliticaPrivacidadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_sem_texto_salvo_a_leitura_publica_vem_vazia(): void
    {
        $this->getJson('/api/v1/politica-privacidade')
            ->assertOk()
            ->assertJsonPath('data.texto', '')
            ->assertJsonPath('data.atualizado_em', null);
    }

    public function test_dono_salva_e_qualquer_um_le_sem_login(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->putJson('/api/v1/politica-privacidade', [
            'texto' => "Primeiro parágrafo.\n\nSegundo.", 'responsavel' => 'Fulano', 'email' => 'privacidade@exemplo.test',
        ])->assertOk();

        $this->app['auth']->forgetGuards();

        $this->getJson('/api/v1/politica-privacidade')
            ->assertOk()
            ->assertJsonPath('data.texto', "Primeiro parágrafo.\n\nSegundo.")
            ->assertJsonPath('data.responsavel', 'Fulano')
            ->assertJsonPath('data.email', 'privacidade@exemplo.test');
    }

    public function test_salvar_de_novo_atualiza_o_mesmo_registro(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->putJson('/api/v1/politica-privacidade', ['texto' => 'Primeiro.'])->assertOk();
        $this->putJson('/api/v1/politica-privacidade', ['texto' => 'Novo.'])->assertOk();

        $this->assertSame(1, PoliticaPrivacidade::count());
        $this->assertSame('Novo.', PoliticaPrivacidade::first()->texto);
    }

    public function test_validacao_e_campo_vazio_vira_nulo(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->putJson('/api/v1/politica-privacidade', ['email' => 'nao-e-email'])->assertUnprocessable();
        $this->putJson('/api/v1/politica-privacidade', ['texto' => str_repeat('a', 40001)])->assertUnprocessable();

        $this->putJson('/api/v1/politica-privacidade', ['texto' => 'X', 'responsavel' => '   '])->assertOk();
        $this->assertNull(PoliticaPrivacidade::first()->responsavel);
    }

    public function test_visitante_e_atendente_nao_editam(): void
    {
        $this->putJson('/api/v1/politica-privacidade', ['texto' => 'x'])->assertUnauthorized();

        Sanctum::actingAs(User::factory()->atendente()->create());
        $this->putJson('/api/v1/politica-privacidade', ['texto' => 'x'])->assertForbidden();

        $this->assertSame(0, PoliticaPrivacidade::count());
    }

    public function test_configuracoes_nao_guardam_politica_propria(): void
    {
        $this->seed(CardapioSeeder::class);
        Sanctum::actingAs(User::factory()->create());

        $resposta = $this->putJson('/api/v1/configuracoes', ['nome' => 'Alfa', 'politica_privacidade' => 'meu texto', 'lgpd_email' => 'a@b.test'])->assertOk();

        $resposta->assertJsonMissingPath('data.politica_privacidade')->assertJsonMissingPath('data.lgpd_email');
        $this->assertNull(Configuracao::first()->politica_privacidade);
    }
}
