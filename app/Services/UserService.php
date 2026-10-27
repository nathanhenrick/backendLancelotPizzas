<?php

namespace App\Services;

use App\Models\User;
use DomainException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class UserService
{
    public function getAll(int $pagina = 1, int $porPagina = 100): LengthAwarePaginator
    {
        return User::query()->orderBy('nome')->paginate($porPagina, ['*'], 'pagina', $pagina);
    }

    public function findById(int $id)
    {
        return User::query()->findOrFail($id);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = new User($data);
            $user->papel ??= 'atendente';
            $user->save();

            return $user;
        });
    }

    public function update(int $id, array $data, ?User $autor)
    {
        return DB::transaction(function () use ($id, $data, $autor) {
            $user = User::query()->findOrFail($id);

            if (empty($data['senha'])) {
                unset($data['senha']);
            }

            if ($user->is($autor) && ($data['status'] ?? 'ATIVO') === 'INATIVO') {
                throw new DomainException('Você não pode desativar o seu próprio acesso.');
            }

            if ($user->is($autor) && ($data['papel'] ?? $user->papel) !== $user->papel) {
                throw new DomainException('Você não pode mudar o seu próprio papel.');
            }

            $deixaDeSerDonoAtivo = $user->ehDono() && $user->status === 'ATIVO'
                && (($data['papel'] ?? $user->papel) !== 'dono' || ($data['status'] ?? $user->status) === 'INATIVO');
            if ($deixaDeSerDonoAtivo) {
                $this->garantirOutroDonoAtivo($user);
            }

            $user->update($data);

            if (isset($data['senha']) || $user->status === 'INATIVO' || $user->wasChanged('papel')) {
                $tokenAtual = $autor?->currentAccessToken();
                $user->tokens()
                    ->when($tokenAtual instanceof PersonalAccessToken, fn($q) => $q->whereKeyNot($tokenAtual->id))
                    ->delete();
            }

            return $user;
        });
    }

    public function delete(int $id, ?User $autor)
    {
        return DB::transaction(function () use ($id, $autor) {
            $user = User::query()->findOrFail($id);

            if ($user->is($autor)) {
                throw new DomainException('Você não pode excluir o seu próprio acesso.');
            }

            if ($user->ehDono() && $user->status === 'ATIVO') {
                $this->garantirOutroDonoAtivo($user);
            }

            $user->tokens()->delete();
            return $user->delete();
        });
    }

    private function garantirOutroDonoAtivo(User $user): void
    {
        $outro = User::query()
            ->where('papel', 'dono')
            ->where('status', 'ATIVO')
            ->whereKeyNot($user->getKey())
            ->exists();

        if (! $outro) {
            throw new DomainException('A pizzaria precisa ter pelo menos um dono ativo.');
        }
    }
}
