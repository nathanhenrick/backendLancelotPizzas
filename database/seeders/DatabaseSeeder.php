<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('app.admin_email');
        $senha = config('app.admin_senha');

        if ((! $email || ! $senha) && app()->environment('testing')) {
            $email = 'admin@montapizza.local';
            $senha = 'password';
        }

        if ($email && $senha && ! User::where('email', $email)->exists()) {
            $admin = new User([
                'nome'   => 'Administrador',
                'email'  => $email,
                'senha'  => $senha,
                'status' => 'ATIVO',
                'papel'  => 'dono',
            ]);
            $admin->save();
        } elseif (! $email || ! $senha) {
            $this->command?->warn('Nenhum administrador criado: defina ADMIN_EMAIL e ADMIN_SENHA no .env e rode o seeder de novo.');
        }

        $this->call(CardapioSeeder::class);
    }
}
