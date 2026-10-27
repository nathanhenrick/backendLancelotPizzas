<?php

namespace Tests\Feature;

use App\Models\Sabor;
use App\Models\User;
use Database\Seeders\CardapioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CardapioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CardapioSeeder::class);
    }

    public function test_site_le_o_cardapio_sem_login(): void
    {
        $this->getJson('/api/v1/configuracoes')->assertOk()->assertJsonPath('data.regra_preco', 'maior');
        $this->getJson('/api/v1/sabores')->assertOk()->assertJsonCount(13, 'data')->assertJsonPath('data.0.faixa', 'esp');
        $this->getJson('/api/v1/bordas')->assertOk()->assertJsonPath('data.1.cor', 0xFFF3D2);
        $this->getJson('/api/v1/bebidas')->assertOk()->assertJsonCount(7, 'data');

        $this->getJson('/api/v1/precos')
            ->assertOk()
            ->assertJsonPath('data.tamanhos.0.id', 'p')
            ->assertJsonPath('data.tamanhos.2.max_sabores', 4)
            ->assertJsonPath('data.faixas.prem.g', 89);
    }

    public function test_painel_cadastra_e_edita_sabor_pela_faixa(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $id = $this->postJson('/api/v1/sabores', [
            'nome' => 'Atum',
            'descricao' => 'Queijo, atum, cebola',
            'faixa' => 'esp',
            'ingredientes' => ['cebola'],
            'ativo' => true,
        ])->assertCreated()->assertJsonPath('data.faixa', 'esp')->json('data.id');

        $this->putJson("/api/v1/sabores/{$id}", ['faixa' => 'prem', 'ativo' => false])
            ->assertOk()
            ->assertJsonPath('data.faixa', 'prem')
            ->assertJsonPath('data.ativo', false);

        $this->postJson('/api/v1/sabores', ['nome' => 'Sem faixa', 'faixa' => 'xyz', 'ingredientes' => [], 'ativo' => true])
            ->assertStatus(422)
            ->assertJsonValidationErrors('faixa');

        $this->deleteJson("/api/v1/sabores/{$id}")->assertOk();
        $this->assertNull(Sabor::find($id));
        $this->deleteJson("/api/v1/sabores/{$id}")->assertNotFound();
    }

    public function test_painel_salva_bordas_com_preco_por_tamanho(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/bordas', ['nome' => 'Chocolate', 'p' => 9, 'm' => 11, 'g' => 13.5, 'cor' => 0x5A3A22, 'ativo' => true])
            ->assertCreated()
            ->assertJsonPath('data.g', 13.5)
            ->assertJsonPath('data.cor', 0x5A3A22);
    }

    public function test_bebida_guarda_modelo_3d_e_cor_do_liquido(): void
    {
        $this->getJson('/api/v1/bebidas')
            ->assertJsonPath('data.0.modelo', 'garrafa_2l')
            ->assertJsonPath('data.0.descricao', 'Cola, guaraná ou laranja')
            ->assertJsonPath('data.0.cor', 0x3B1A0E);

        Sanctum::actingAs(User::factory()->create());

        $id = $this->postJson('/api/v1/bebidas', ['nome' => 'Vinho tinto', 'preco' => 45, 'modelo' => 'taca', 'cor' => 0x5E0B1C, 'ativo' => true])
            ->assertCreated()
            ->assertJsonPath('data.modelo', 'taca')
            ->json('data.id');

        $this->putJson("/api/v1/bebidas/{$id}", ['modelo' => null])
            ->assertOk()
            ->assertJsonPath('data.modelo', null);

        $this->postJson('/api/v1/bebidas', ['nome' => 'X', 'preco' => 1, 'modelo' => 'barril', 'ativo' => true])
            ->assertStatus(422)
            ->assertJsonValidationErrors('modelo');
    }

    public function test_painel_atualiza_tamanhos_e_precos_juntos(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $tabela = $this->getJson('/api/v1/precos')->json('data');
        $tabela['tamanhos'][0]['max_sabores'] = 1;
        $tabela['faixas']['trad']['g'] = 70;

        $this->putJson('/api/v1/precos', $tabela)
            ->assertOk()
            ->assertJsonPath('data.tamanhos.0.max_sabores', 1)
            ->assertJsonPath('data.faixas.trad.g', 70);

        $tabela['faixas']['nova'] = ['nome' => 'Nova', 'p' => 1, 'm' => 1, 'g' => 1];
        $this->putJson('/api/v1/precos', $tabela)->assertStatus(422);
    }

    public function test_whatsapp_e_guardado_so_com_digitos(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->putJson('/api/v1/configuracoes', ['nome' => 'Pizzaria Boa', 'whatsapp' => '+55 (44) 99999-0000', 'regra_preco' => 'media'])
            ->assertOk()
            ->assertJsonPath('data.whatsapp', '5544999990000')
            ->assertJsonPath('data.regra_preco', 'media');

        $this->putJson('/api/v1/configuracoes', ['whatsapp' => '123'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('whatsapp');
    }

    public function test_dados_da_empresa_sao_salvos_e_publicos(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $horarios = array_map(fn ($d) => ['dia' => $d, 'fechado' => false, 'abre' => '18:00', 'fecha' => '23:00'],
            ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo']);

        $this->putJson('/api/v1/configuracoes', [
            'cnpj' => '12.345.678/0001-95', 'cep' => '87500-000', 'uf' => 'pr', 'email' => 'oi@pizza.com',
            'telefone' => '(44) 3622-0000', 'horarios' => $horarios,
        ])->assertOk()
            ->assertJsonPath('data.cnpj', '12345678000195')
            ->assertJsonPath('data.cep', '87500000')
            ->assertJsonPath('data.uf', 'PR')
            ->assertJsonPath('data.horarios.0.abre', '18:00');

        $this->putJson('/api/v1/configuracoes', ['cnpj' => '123'])->assertStatus(422)->assertJsonValidationErrors('cnpj');
        $this->putJson('/api/v1/configuracoes', ['horarios' => [['dia' => 'x', 'fechado' => false]]])->assertStatus(422);
    }
}
