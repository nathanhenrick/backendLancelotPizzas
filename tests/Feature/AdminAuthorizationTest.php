<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cadastro_administrativo_publico_nao_existe(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'nome' => 'Visitante',
            'email' => 'visitante@example.com',
            'senha' => 'senha-segura-123',
        ])->assertNotFound();
    }

    public function test_login_devolve_token_e_usuario(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);

        $this->postJson('/api/v1/auth/login', ['email' => 'admin@example.com', 'senha' => 'password'])
            ->assertOk()
            ->assertJsonPath('data.user.id', $admin->id)
            ->assertJsonMissingPath('data.user.senha')
            ->assertJsonStructure(['data' => ['token']]);
    }

    public function test_login_com_senha_errada_e_recusado(): void
    {
        User::factory()->create(['email' => 'admin@example.com']);

        $this->postJson('/api/v1/auth/login', ['email' => 'admin@example.com', 'senha' => 'errada'])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Credenciais inválidas.');
    }

    public function test_usuario_inativo_nao_entra(): void
    {
        User::factory()->inativo()->create(['email' => 'inativo@example.com']);

        $this->postJson('/api/v1/auth/login', ['email' => 'inativo@example.com', 'senha' => 'password'])
            ->assertUnauthorized();
    }

    public function test_visitante_nao_pode_acessar_rota_administrativa(): void
    {
        $this->getJson('/api/v1/dashboard')->assertUnauthorized();
        $this->getJson('/api/v1/admin/pedidos')->assertUnauthorized();
        $this->getJson('/api/v1/users')->assertUnauthorized();
    }

    public function test_token_de_usuario_desativado_perde_acesso(): void
    {
        Sanctum::actingAs(User::factory()->inativo()->create());

        $this->getJson('/api/v1/dashboard')
            ->assertForbidden()
            ->assertJsonPath('message', 'Acesso restrito a administradores.');
    }

    public function test_administrador_pode_acessar_rota_administrativa(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/dashboard')->assertOk();
    }

    public function test_logout_administrativo_revoga_o_token_atual(): void
    {
        $admin = User::factory()->create();
        $plainTextToken = $admin->createToken('qa-admin')->plainTextToken;

        $this->withToken($plainTextToken)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->app['auth']->forgetGuards();
        $this->withToken($plainTextToken)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    }

    public function test_visitante_nao_alcanca_operacoes_administrativas_de_escrita(): void
    {
        foreach (['/api/v1/users', '/api/v1/sabores', '/api/v1/bordas', '/api/v1/bebidas'] as $endpoint) {
            $this->postJson($endpoint, [])->assertUnauthorized();
        }

        foreach (['/api/v1/configuracoes', '/api/v1/precos', '/api/v1/pedidos/1', '/api/v1/sabores/1'] as $endpoint) {
            $this->putJson($endpoint, [])->assertUnauthorized();
        }

        $this->deleteJson('/api/v1/sabores/1')->assertUnauthorized();
    }

    public function test_administrador_nao_exclui_nem_desativa_o_proprio_acesso(): void
    {
        $admin = User::factory()->create();
        Sanctum::actingAs($admin);

        $this->deleteJson("/api/v1/users/{$admin->id}")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Você não pode excluir o seu próprio acesso.');

        $this->putJson("/api/v1/users/{$admin->id}", ['status' => 'INATIVO'])
            ->assertStatus(422);
    }

    public function test_administrador_cadastra_outro_usuario_que_consegue_entrar(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/users', [
            'nome' => 'Atendente',
            'email' => 'atendente@example.com',
            'senha' => 'senha-forte-123',
            'status' => 'ATIVO',
        ])->assertCreated()->assertJsonMissingPath('data.senha');

        $this->app['auth']->forgetGuards();
        $this->postJson('/api/v1/auth/login', ['email' => 'atendente@example.com', 'senha' => 'senha-forte-123'])
            ->assertOk();
    }
}
