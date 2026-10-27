<?php

namespace Tests\Feature;

use App\Models\Adicional;
use App\Models\Bebida;
use App\Models\Borda;
use App\Models\Configuracao;
use App\Models\Esfirra;
use App\Models\Pedido;
use App\Models\Sabor;
use App\Models\User;
use Database\Seeders\CardapioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PedidoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CardapioSeeder::class);
    }

    private function sabor(string $nome): int
    {
        return Sabor::where('nome', $nome)->value('id');
    }

    public function test_preco_e_calculado_no_servidor_pelo_sabor_de_maior_valor(): void
    {
        $catupiry = Borda::where('nome', 'Recheada com catupiry')->value('id');
        $lata = Bebida::where('nome', 'Refrigerante lata')->value('id');

        $response = $this->postJson('/api/v1/pedidos', [
            'cliente' => '  Ana  ',
            'modo' => 'entrega',
            'tamanho' => 'g',
            'sabores' => [$this->sabor('Calabresa'), $this->sabor('Pepperoni')],
            'borda' => $catupiry,
            'bebidas' => [['id' => $lata, 'quantidade' => 2]],
            'total' => 1,
            'status' => 'concluido',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.cliente', 'Ana')
            ->assertJsonPath('data.status', 'novo')
            ->assertJsonPath('data.total', 113)
            ->assertJsonPath('data.sabores', ['Calabresa', 'Pepperoni'])
            ->assertJsonPath('data.linhas.0.titulo', 'Pizza Grande')
            ->assertJsonPath('data.linhas.0.detalhes', ['1/2 Calabresa', '1/2 Pepperoni'])
            ->assertJsonPath('data.linhas.0.preco', 89)
            ->assertJsonPath('data.linhas.1.detalhes', ['Recheada com catupiry'])
            ->assertJsonPath('data.linhas.2.titulo', '2× Refrigerante lata');

        $this->assertSame(113.0, Pedido::first()->valor_total);
    }

    public function test_ajustes_do_sabor_esfirras_e_adicionais_entram_no_total(): void
    {
        $bacon = Adicional::where('nome', 'Bacon')->value('id');
        $esfirra = Esfirra::where('nome', 'Esfirra de carne')->value('id');

        $this->postJson('/api/v1/pedidos', [
            'modo' => 'retirada',
            'tamanho' => 'g',
            'sabores' => [$this->sabor('Calabresa'), $this->sabor('Brigadeiro')],
            'ajustes' => [
                ['removidos' => ['Cebola'], 'adicionais' => [$bacon], 'observacao' => 'bem assado'],
                ['removidos' => [], 'adicionais' => []],
            ],
            'esfirras' => [['id' => $esfirra, 'quantidade' => 3]],
        ])->assertCreated()
            ->assertJsonPath('data.total', 98)
            ->assertJsonPath('data.linhas.0.detalhes.0', '1/2 Calabresa (sem cebola; + Bacon; obs: bem assado)')
            ->assertJsonPath('data.linhas.0.detalhes.1', '1/2 Brigadeiro')
            ->assertJsonPath('data.linhas.1.titulo', 'Adicionais')
            ->assertJsonPath('data.linhas.2.titulo', '3× Esfirra de carne (Pequena)');
    }

    public function test_adicional_inativo_e_recusado(): void
    {
        $bacon = Adicional::where('nome', 'Bacon')->first();
        $bacon->update(['ativo' => false]);

        $this->postJson('/api/v1/pedidos', [
            'modo' => 'retirada',
            'tamanho' => 'g',
            'sabores' => [$this->sabor('Calabresa')],
            'ajustes' => [['adicionais' => [$bacon->id]]],
        ])->assertUnprocessable();
    }

    public function test_sabor_so_aceita_os_adicionais_cadastrados_para_ele(): void
    {
        $bacon = Adicional::where('nome', 'Bacon')->value('id');
        Sabor::where('nome', 'Calabresa')->first()->update(['adicionais' => []]);

        $this->postJson('/api/v1/pedidos', [
            'modo' => 'retirada',
            'tamanho' => 'g',
            'sabores' => [$this->sabor('Calabresa')],
            'ajustes' => [['adicionais' => [$bacon]]],
        ])->assertUnprocessable();
    }

    public function test_pedido_so_de_esfirras_sem_pizza(): void
    {
        $esfirra = Esfirra::where('nome', 'Esfirra de carne')->value('id');

        $this->postJson('/api/v1/pedidos', [
            'modo' => 'retirada',
            'esfirras' => [['id' => $esfirra, 'quantidade' => 4]],
        ])->assertCreated()
            ->assertJsonPath('data.total', 20)
            ->assertJsonPath('data.sabores', [])
            ->assertJsonPath('data.linhas.0.titulo', '4× Esfirra de carne (Pequena)');

        $this->postJson('/api/v1/pedidos', ['modo' => 'retirada'])->assertUnprocessable();
    }

    public function test_esfirra_com_tamanhos_cobra_o_preco_do_tamanho(): void
    {
        $esfirra = Esfirra::where('nome', 'Esfirra de carne')->first();
        $esfirra->update(['tamanhos' => [['nome' => 'Mini', 'preco' => 3], ['nome' => 'Grande', 'preco' => 8]]]);

        $this->postJson('/api/v1/pedidos', [
            'modo' => 'retirada',
            'esfirras' => [
                ['id' => $esfirra->id, 'tamanho' => 'Mini', 'quantidade' => 2],
                ['id' => $esfirra->id, 'tamanho' => 'Grande', 'quantidade' => 1],
            ],
        ])->assertCreated()
            ->assertJsonPath('data.total', 14)
            ->assertJsonPath('data.linhas.0.titulo', '2× Esfirra de carne (Mini)')
            ->assertJsonPath('data.linhas.1.titulo', '1× Esfirra de carne (Grande)');

        $this->postJson('/api/v1/pedidos', [
            'modo' => 'retirada',
            'esfirras' => [['id' => $esfirra->id, 'tamanho' => 'Gigante', 'quantidade' => 1]],
        ])->assertUnprocessable();
    }

    public function test_esfirra_salgada_aceita_ajustes_e_doce_nao_aceita_adicional(): void
    {
        $bacon = Adicional::where('nome', 'Bacon')->value('id');
        $esfirra = Esfirra::where('nome', 'Esfirra de carne')->first();
        $esfirra->update(['tamanhos' => null, 'adicionais' => [$bacon]]);

        $this->postJson('/api/v1/pedidos', [
            'modo' => 'retirada',
            'esfirras' => [['id' => $esfirra->id, 'quantidade' => 2, 'ajuste' => ['removidos' => ['Cebola'], 'adicionais' => [$bacon], 'observacao' => 'bem assada']]],
        ])->assertCreated()
            ->assertJsonPath('data.total', 20)
            ->assertJsonPath('data.linhas.0.detalhes.0', 'sem cebola; + Bacon; obs: bem assada');

        $esfirra->update(['tipo' => 'doce']);
        $this->postJson('/api/v1/pedidos', [
            'modo' => 'retirada',
            'esfirras' => [['id' => $esfirra->id, 'quantidade' => 1, 'ajuste' => ['adicionais' => [$bacon]]]],
        ])->assertUnprocessable();
    }

    public function test_regra_da_media_dos_sabores(): void
    {
        Configuracao::first()->update(['regra_preco' => 'media']);

        $this->postJson('/api/v1/pedidos', [
            'modo' => 'retirada',
            'tamanho' => 'g',
            'sabores' => [$this->sabor('Calabresa'), $this->sabor('Bacon')],
        ])->assertCreated()->assertJsonPath('data.total', 73);
    }

    public function test_borda_tradicional_gratis_nao_vira_linha(): void
    {
        $tradicional = Borda::where('nome', 'Tradicional')->value('id');

        $this->postJson('/api/v1/pedidos', [
            'modo' => 'entrega',
            'tamanho' => 'p',
            'sabores' => [$this->sabor('Mussarela')],
            'borda' => $tradicional,
        ])->assertCreated()
            ->assertJsonCount(1, 'data.linhas')
            ->assertJsonPath('data.linhas.0.detalhes', ['Mussarela'])
            ->assertJsonPath('data.total', 39);
    }

    public function test_recusa_mais_sabores_que_o_tamanho_aceita(): void
    {
        $id = $this->sabor('Calabresa');

        $this->postJson('/api/v1/pedidos', [
            'modo' => 'entrega',
            'tamanho' => 'p',
            'sabores' => [$id, $id, $id],
        ])->assertStatus(422)->assertJsonValidationErrors('sabores');
    }

    public function test_recusa_sabor_desativado(): void
    {
        $sabor = Sabor::where('nome', 'Bacon')->first();
        $sabor->update(['ativo' => false]);

        $this->postJson('/api/v1/pedidos', [
            'modo' => 'entrega',
            'tamanho' => 'g',
            'sabores' => [$sabor->id],
        ])->assertStatus(422)->assertJsonValidationErrors('sabores');
    }

    public function test_visitante_nao_lista_nem_altera_pedidos(): void
    {
        $this->getJson('/api/v1/admin/pedidos')->assertUnauthorized();
        $this->putJson('/api/v1/pedidos/1', ['status' => 'cancelado'])->assertUnauthorized();
    }

    public function test_painel_lista_e_avanca_a_situacao_do_pedido(): void
    {
        $id = $this->postJson('/api/v1/pedidos', [
            'modo' => 'entrega',
            'tamanho' => 'm',
            'sabores' => [$this->sabor('Marguerita')],
        ])->json('data.id');

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/admin/pedidos')
            ->assertOk()
            ->assertJsonPath('data.0.id', $id);

        $this->putJson("/api/v1/pedidos/{$id}", ['status' => 'preparo'])
            ->assertOk()
            ->assertJsonPath('data.status', 'preparo');

        $this->putJson("/api/v1/pedidos/{$id}", ['status' => 'qualquer'])
            ->assertStatus(422);

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('data.pedidos_hoje', 1)
            ->assertJsonPath('data.aguardando', 1)
            ->assertJsonPath('data.sabores_mais_pedidos.0.nome', 'Marguerita');
    }

    public function test_ajustes_fora_de_ordem_nao_burlam_os_adicionais_de_cada_sabor(): void
    {
        $bacon = Adicional::where('nome', 'Bacon')->value('id');
        Sabor::where('nome', 'Calabresa')->first()->update(['adicionais' => []]);
        Sabor::where('nome', 'Brigadeiro')->first()->update(['adicionais' => [$bacon]]);

        $this->postJson('/api/v1/pedidos', [
            'modo' => 'retirada',
            'tamanho' => 'g',
            'sabores' => [$this->sabor('Calabresa'), $this->sabor('Brigadeiro')],
            'ajustes' => ['1' => ['adicionais' => []], '0' => ['adicionais' => [$bacon]]],
        ])->assertUnprocessable();

        $this->postJson('/api/v1/pedidos', [
            'modo' => 'retirada',
            'tamanho' => 'g',
            'sabores' => [$this->sabor('Calabresa'), $this->sabor('Brigadeiro')],
            'ajustes' => ['1' => ['adicionais' => [$bacon]], '0' => ['adicionais' => []]],
        ])->assertCreated();
    }

    public function test_adicional_inativo_da_esfirra_e_recusado(): void
    {
        $bacon = Adicional::where('nome', 'Bacon')->first();
        $esfirra = Esfirra::where('nome', 'Esfirra de carne')->first();
        $esfirra->update(['tamanhos' => null, 'adicionais' => [$bacon->id]]);
        $bacon->update(['ativo' => false]);

        $this->postJson('/api/v1/pedidos', [
            'modo' => 'retirada',
            'esfirras' => [['id' => $esfirra->id, 'quantidade' => 1, 'ajuste' => ['adicionais' => [$bacon->id]]]],
        ])->assertUnprocessable();
    }
}
