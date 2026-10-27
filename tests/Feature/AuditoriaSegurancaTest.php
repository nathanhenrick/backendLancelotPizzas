<?php

namespace Tests\Feature;

use App\Models\Bebida;
use App\Models\Pedido;
use App\Models\Sabor;
use App\Models\User;
use App\Providers\AppServiceProvider;
use App\Services\UserService;
use Database\Seeders\CardapioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuditoriaSegurancaTest extends TestCase
{
    use RefreshDatabase;

    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    private User $dono;
    private User $atendente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CardapioSeeder::class);

        $this->dono = User::factory()->create();
        $this->atendente = User::factory()->atendente()->create();
    }

    protected function tearDown(): void
    {
        TrustProxies::flushState();
        parent::tearDown();
    }

    private function pedido(string $status = 'novo'): Pedido
    {
        return Pedido::create([
            'cliente' => 'Ana', 'modo' => 'retirada', 'status' => $status, 'tamanho' => 'Grande',
            'sabores' => ['Calabresa'], 'valor_pizza' => 68, 'valor_total' => 68,
        ]);
    }

    private function confiarNosProxies(string $proxies = '*'): void
    {
        config(['app.trusted_proxies' => $proxies]);
        (new AppServiceProvider($this->app))->boot();
    }

    public static function rotasSoDoDono(): array
    {
        return [
            'listar usuários' => ['GET', '/api/v1/users'],
            'criar usuário' => ['POST', '/api/v1/users'],
            'editar usuário' => ['PUT', '/api/v1/users/1'],
            'excluir usuário' => ['DELETE', '/api/v1/users/1'],
            'dashboard' => ['GET', '/api/v1/dashboard'],
            'configurações' => ['PUT', '/api/v1/configuracoes'],
            'preços' => ['PUT', '/api/v1/precos'],
            'criar sabor' => ['POST', '/api/v1/sabores'],
            'editar sabor' => ['PUT', '/api/v1/sabores/1'],
            'excluir sabor' => ['DELETE', '/api/v1/sabores/1'],
            'criar borda' => ['POST', '/api/v1/bordas'],
            'excluir bebida' => ['DELETE', '/api/v1/bebidas/1'],
            'editar esfirra' => ['PUT', '/api/v1/esfirras/1'],
            'excluir adicional' => ['DELETE', '/api/v1/adicionais/1'],
        ];
    }

    #[DataProvider('rotasSoDoDono')]
    public function test_atendente_nao_alcanca_rotas_do_dono(string $metodo, string $rota): void
    {
        Sanctum::actingAs($this->atendente);

        $this->json($metodo, $rota, [])->assertForbidden();
    }

    public function test_atendente_cuida_dos_pedidos(): void
    {
        $pedido = $this->pedido();
        Sanctum::actingAs($this->atendente);

        $this->getJson('/api/v1/auth/me')->assertOk()->assertJsonPath('data.papel', 'atendente');
        $this->getJson('/api/v1/admin/pedidos')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/v1/pedidos/{$pedido->id}")->assertOk();
        $this->putJson("/api/v1/pedidos/{$pedido->id}", ['status' => 'preparo'])->assertOk();
    }

    public function test_dono_continua_com_acesso_total(): void
    {
        Sanctum::actingAs($this->dono);

        $this->getJson('/api/v1/users')->assertOk();
        $this->getJson('/api/v1/dashboard')->assertOk();
        $this->putJson('/api/v1/configuracoes', ['nome' => 'Nova'])->assertOk();
    }

    public function test_usuario_sem_papel_informado_nasce_atendente_e_papel_invalido_e_recusado(): void
    {
        Sanctum::actingAs($this->dono);
        $corpo = ['nome' => 'Novo', 'email' => 'novo@example.com', 'senha' => 'senha-segura-123', 'status' => 'ATIVO'];

        $criado = $this->postJson('/api/v1/users', $corpo)->assertCreated()->assertJsonPath('data.papel', 'atendente');
        $this->assertSame('atendente', User::find($criado->json('data.id'))->papel);

        $this->postJson('/api/v1/users', ['email' => 'outro@example.com', 'papel' => 'superdono'] + $corpo)
            ->assertUnprocessable()->assertJsonValidationErrors('papel');
    }

    public function test_dono_nao_muda_o_proprio_papel(): void
    {
        Sanctum::actingAs($this->dono);

        $this->putJson("/api/v1/users/{$this->dono->id}", ['papel' => 'atendente'])->assertUnprocessable();
        $this->assertSame('dono', $this->dono->fresh()->papel);
    }

    public function test_mudar_o_papel_de_alguem_derruba_as_sessoes_dessa_pessoa(): void
    {
        $outroDono = User::factory()->create();
        $outroDono->createToken('t');
        $this->dono->createToken('t');

        app(UserService::class)->update($outroDono->id, ['papel' => 'atendente'], $this->dono);

        $this->assertSame('atendente', $outroDono->fresh()->papel);
        $this->assertSame(0, $outroDono->tokens()->count());
        $this->assertSame(1, $this->dono->tokens()->count());
    }

    private function tentarLogin(string $email, string $ip): int
    {
        return $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->postJson('/api/v1/auth/login', ['email' => $email, 'senha' => 'errada'], ['X-Forwarded-For' => $ip])
            ->status();
    }

    public function test_sem_proxy_confiavel_o_cabecalho_nao_burla_o_limite_de_login(): void
    {
        $status = [];
        foreach (range(1, 11) as $i) {
            $status[] = $this->tentarLogin("pessoa{$i}@example.com", "203.0.113.{$i}");
        }

        $this->assertSame(429, end($status), 'o 11º login do mesmo IP real deveria ser limitado');
    }

    public function test_com_proxy_confiavel_cada_cliente_tem_o_seu_limite(): void
    {
        $this->confiarNosProxies();

        foreach (range(1, 12) as $i) {
            $this->assertSame(401, $this->tentarLogin("pessoa{$i}@example.com", "203.0.113.{$i}"));
        }
    }

    public function test_limite_de_login_tambem_vale_por_e_mail(): void
    {
        $this->confiarNosProxies();

        foreach (range(1, 5) as $i) {
            $this->assertSame(401, $this->tentarLogin('alvo@example.com', "203.0.113.{$i}"));
        }

        $this->assertSame(429, $this->tentarLogin('alvo@example.com', '203.0.113.99'));
        $this->assertSame(401, $this->tentarLogin('outra@example.com', '203.0.113.99'));
    }

    public function test_pedidos_tem_teto_geral_mesmo_vindo_de_muitos_ips(): void
    {
        $this->confiarNosProxies();

        foreach (range(1, 120) as $i) {
            $resposta = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
                ->postJson('/api/v1/pedidos', ['modo' => 'retirada'], ['X-Forwarded-For' => "198.51.100.{$i}"]);
            $this->assertNotSame(429, $resposta->status(), "pedido {$i} não deveria ser limitado");
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->postJson('/api/v1/pedidos', ['modo' => 'retirada'], ['X-Forwarded-For' => '198.51.100.200'])
            ->assertStatus(429);
    }

    private function desativarUmSabor(): int
    {
        $sabor = Sabor::first();
        $sabor->update(['ativo' => false]);

        return $sabor->id;
    }

    public function test_site_nao_recebe_itens_desativados(): void
    {
        $id = $this->desativarUmSabor();

        $ids = collect($this->getJson('/api/v1/sabores')->assertOk()->json('data'))->pluck('id');
        $this->assertNotContains($id, $ids->all());
        $this->getJson("/api/v1/sabores/{$id}")->assertNotFound();
    }

    public function test_dono_logado_ve_os_desativados(): void
    {
        $id = $this->desativarUmSabor();
        Sanctum::actingAs($this->dono);

        $ids = collect($this->getJson('/api/v1/sabores')->assertOk()->json('data'))->pluck('id');
        $this->assertContains($id, $ids->all());
        $this->getJson("/api/v1/sabores/{$id}")->assertOk();
    }

    public function test_atendente_nao_ve_os_desativados(): void
    {
        $id = $this->desativarUmSabor();

        Sanctum::actingAs($this->atendente);
        $this->getJson("/api/v1/sabores/{$id}")->assertNotFound();
    }

    public function test_lista_de_pedidos_e_paginada_com_meta(): void
    {
        foreach (range(1, 5) as $i) {
            $this->pedido();
        }
        Sanctum::actingAs($this->dono);

        $primeira = $this->getJson('/api/v1/admin/pedidos?por_pagina=2')->assertOk();
        $primeira->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 5)
            ->assertJsonPath('meta.por_pagina', 2)
            ->assertJsonPath('meta.ultima_pagina', 3);

        $this->getJson('/api/v1/admin/pedidos?por_pagina=2&pagina=3')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/admin/pedidos')->assertOk()->assertJsonCount(5, 'data')->assertJsonPath('meta.por_pagina', 200);
        $this->getJson('/api/v1/admin/pedidos?por_pagina=99999')->assertOk()->assertJsonPath('meta.por_pagina', 500);
        $this->getJson('/api/v1/admin/pedidos?por_pagina=0&pagina=-4')->assertOk()->assertJsonPath('meta.por_pagina', 1)->assertJsonPath('meta.pagina', 1);
    }

    public function test_lista_de_usuarios_e_paginada_com_meta(): void
    {
        User::factory()->count(3)->create();
        Sanctum::actingAs($this->dono);

        $this->getJson('/api/v1/users?por_pagina=2')->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 5)
            ->assertJsonPath('meta.ultima_pagina', 3);
        $this->getJson('/api/v1/users?por_pagina=999')->assertOk()->assertJsonPath('meta.por_pagina', 200);
    }

    public function test_login_com_e_mail_inexistente_tambem_confere_um_hash(): void
    {
        Hash::partialMock()->shouldReceive('check')->once()->andReturn(false);

        $this->postJson('/api/v1/auth/login', ['email' => 'ninguem@example.com', 'senha' => 'qualquer-coisa'])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Credenciais inválidas.');
    }

    public function test_login_com_senha_errada_e_com_e_mail_inexistente_respondem_igual(): void
    {
        $existente = $this->postJson('/api/v1/auth/login', ['email' => $this->dono->email, 'senha' => 'errada'])->json();
        $inexistente = $this->postJson('/api/v1/auth/login', ['email' => 'ninguem@example.com', 'senha' => 'errada'])->json();

        $this->assertSame($existente, $inexistente);
    }

    private function bebida(array $extra = []): array
    {
        return ['nome' => 'Cola', 'preco' => 6, 'ativo' => true] + $extra;
    }

    public function test_logo_png_valido_e_aceito(): void
    {
        Sanctum::actingAs($this->dono);

        $this->postJson('/api/v1/bebidas', $this->bebida(['logo' => 'data:image/png;base64,' . self::PNG]))->assertCreated();
    }

    public static function logosInvalidas(): array
    {
        $svg = base64_encode('<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

        return [
            'svg com script' => ["data:image/svg+xml;base64,{$svg}"],
            'html disfarçado' => ['data:image/png;base64,' . base64_encode('<html><script>alert(1)</script></html>')],
            'tipo declarado diferente do conteúdo' => ['data:image/jpeg;base64,' . self::PNG],
            'sem base64' => ['data:image/png,abc'],
            'esquema perigoso' => ['javascript:alert(1)'],
            'base64 inválido' => ['data:image/png;base64,@@@@'],
        ];
    }

    #[DataProvider('logosInvalidas')]
    public function test_logo_invalida_e_recusada(string $logo): void
    {
        Sanctum::actingAs($this->dono);

        $this->postJson('/api/v1/bebidas', $this->bebida(['logo' => $logo]))
            ->assertUnprocessable()->assertJsonValidationErrors('logo');
        $this->assertSame(0, Bebida::where('nome', 'Cola')->count());
    }

    public function test_logo_antiga_inalterada_nao_trava_a_edicao_mas_outra_svg_e_recusada(): void
    {
        $antiga = 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg"><text>X</text></svg>');
        $id = Bebida::create(['nome' => 'Antiga', 'preco' => 5, 'ativo' => true, 'logo' => $antiga])->id;
        Sanctum::actingAs($this->dono);

        $this->putJson("/api/v1/bebidas/{$id}", ['nome' => 'Renomeada', 'logo' => $antiga])->assertOk();
        $this->putJson("/api/v1/bebidas/{$id}", ['logo' => 'data:image/svg+xml;base64,' . base64_encode('<svg><script/></svg>')])
            ->assertUnprocessable();
    }

    public static function transicoes(): array
    {
        return [
            'novo para preparo' => ['novo', 'preparo', 200],
            'preparo para saiu' => ['preparo', 'saiu', 200],
            'saiu para concluído' => ['saiu', 'concluido', 200],
            'novo para cancelado' => ['novo', 'cancelado', 200],
            'retirada: preparo direto para concluído' => ['preparo', 'concluido', 200],
            'mesma situação de novo' => ['preparo', 'preparo', 200],
            'concluído não volta' => ['concluido', 'novo', 422],
            'concluído não é cancelado depois' => ['concluido', 'cancelado', 422],
            'cancelado não reabre' => ['cancelado', 'preparo', 422],
            'não volta para novo' => ['preparo', 'novo', 422],
            'não volta de saiu' => ['saiu', 'preparo', 422],
        ];
    }

    #[DataProvider('transicoes')]
    public function test_transicoes_de_situacao_do_pedido(string $de, string $para, int $esperado): void
    {
        $pedido = $this->pedido($de);
        Sanctum::actingAs($this->dono);

        $this->putJson("/api/v1/pedidos/{$pedido->id}", ['status' => $para])->assertStatus($esperado);

        $this->assertSame($esperado === 200 ? $para : $de, Pedido::find($pedido->id)->status);
    }

    public function test_email_ja_usado_e_recusado(): void
    {
        $existente = User::factory()->create(['email' => 'repetido@example.com']);
        Sanctum::actingAs($this->dono);

        $this->postJson('/api/v1/users', ['nome' => 'Dup', 'email' => $existente->email, 'senha' => 'senha-segura-123', 'status' => 'ATIVO'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_cors_so_libera_as_origens_configuradas(): void
    {
        $permitida = $this->withHeaders(['Origin' => 'http://localhost:3000', 'Access-Control-Request-Method' => 'GET'])
            ->options('/api/v1/sabores');
        $this->assertSame('http://localhost:3000', $permitida->headers->get('Access-Control-Allow-Origin'));

        $negada = $this->withHeaders(['Origin' => 'https://site-malicioso.example', 'Access-Control-Request-Method' => 'GET'])
            ->options('/api/v1/sabores');
        $this->assertNull($negada->headers->get('Access-Control-Allow-Origin'));
        $this->assertNull($negada->headers->get('Access-Control-Allow-Credentials'));
    }
}
