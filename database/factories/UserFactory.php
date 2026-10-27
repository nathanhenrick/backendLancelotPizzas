<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected static ?string $senha;

    public function definition(): array
    {
        return [
            'nome'   => fake()->name(),
            'email'  => fake()->unique()->safeEmail(),
            'senha'  => static::$senha ??= Hash::make('password'),
            'status' => 'ATIVO',
            'papel' => 'dono',
        ];
    }

    public function atendente(): static
    {
        return $this->state(fn () => ['papel' => 'atendente']);
    }

    public function inativo(): static
    {
        return $this->state(fn () => ['status' => 'INATIVO']);
    }
}
