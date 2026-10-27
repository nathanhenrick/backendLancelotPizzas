<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\AppServiceProvider;
use App\Services\SaborService;
use Database\Seeders\DatabaseSeeder;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_erro_500_nao_devolve_detalhe_tecnico_e_registra_no_log(): void
    {
        Log::spy();

        $segredo = 'SQLSTATE[42S02]: select * from `usuarios_secretos` where token = abc123';
        $this->mock(SaborService::class)
            ->shouldReceive('getAll')
            ->andThrow(new Exception($segredo));

        $resposta = $this->getJson('/api/v1/sabores')
            ->assertStatus(500)
            ->assertJsonPath('status', 'Error')
            ->assertJsonPath('errors', []);

        $this->assertStringNotContainsString('SQLSTATE', $resposta->getContent());
        $this->assertStringNotContainsString('usuarios_secretos', $resposta->getContent());

        Log::shouldHaveReceived('error')
            ->once()
            ->withArgs(fn ($mensagem, $contexto) => ($contexto['detalhe'] ?? null) === $segredo);
    }

    public function test_erros_4xx_continuam_devolvendo_as_mensagens_de_validacao(): void
    {
        $this->postJson('/api/v1/auth/login', ['email' => 'nao-e-email'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'senha']);

        User::factory()->create(['email' => 'a@example.com']);
        $this->postJson('/api/v1/auth/login', ['email' => 'a@example.com', 'senha' => 'errada'])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Credenciais inválidas.');
    }

    public function test_env_example_nao_liga_o_debug(): void
    {
        $linhas = file(base_path('.env.example'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $ativas = array_filter($linhas, fn ($linha) => ! str_starts_with(ltrim($linha), '#'));

        $this->assertContains('APP_DEBUG=false', $ativas);
        $this->assertNotContains('APP_DEBUG=true', $ativas);
    }

    public function test_producao_com_debug_ligado_nao_sobe(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['app.debug' => true]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_DEBUG');

        (new AppServiceProvider($this->app))->boot();
    }

    public function test_producao_sem_debug_sobe_normalmente(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['app.debug' => false]);

        (new AppServiceProvider($this->app))->boot();

        $this->addToAssertionCount(1);
    }

    public static function ambientesSemPadrao(): array
    {
        return ['local' => ['local'], 'producao' => ['production'], 'staging' => ['staging']];
    }

    #[DataProvider('ambientesSemPadrao')]
    public function test_seeder_nao_cria_admin_padrao_fora_dos_testes(string $ambiente): void
    {
        $this->app->detectEnvironment(fn () => $ambiente);
        config(['app.admin_email' => null, 'app.admin_senha' => null]);

        $this->app->make(DatabaseSeeder::class)->run();

        $this->assertSame(0, User::count());
        $this->assertSame(0, User::where('email', 'admin@montapizza.local')->count());
    }

    public function test_seeder_cria_o_admin_do_env_em_qualquer_ambiente(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['app.admin_email' => 'dono@pizzaria.test', 'app.admin_senha' => 'uma-senha-forte-123']);

        $this->app->make(DatabaseSeeder::class)->run();

        $admin = User::where('email', 'dono@pizzaria.test')->firstOrFail();
        $this->assertSame('dono', $admin->papel);
        $this->assertSame(1, User::count());
    }

    public function test_seeder_nos_testes_mantem_o_admin_padrao(): void
    {
        config(['app.admin_email' => null, 'app.admin_senha' => null]);

        $this->app->make(DatabaseSeeder::class)->run();

        $this->assertSame(1, User::where('email', 'admin@montapizza.local')->count());
    }
}
