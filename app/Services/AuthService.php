<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    private static ?string $hashFalso = null;

    public function login(array $credentials)
    {
        $user = User::where('email', $credentials['email'])->first();

        $hash = $user?->senha ?? self::$hashFalso ??= Hash::make(Str::random(40));

        if (! Hash::check($credentials['senha'], $hash) || ! $user) {
            throw new Exception('Credenciais inválidas.');
        }

        if ($user->status === 'INATIVO') {
            throw new Exception('Conta inativa. Entre em contato com o suporte.');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token
        ];
    }
}
