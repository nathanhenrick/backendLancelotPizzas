<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'status',
        'papel',
    ];

    public const PAPEIS = ['dono', 'atendente'];

    protected $attributes = ['papel' => 'atendente'];

    protected $hidden = [
        'senha',
    ];

    public function ehDono(): bool
    {
        return $this->papel === 'dono';
    }

    public function getAuthPassword()
    {
        return $this->senha;
    }

    protected function casts(): array
    {
        return [
            'senha' => 'hashed',
        ];
    }
}
