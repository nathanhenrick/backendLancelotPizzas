<?php

namespace App\Services;

use App\Models\Borda;
use Illuminate\Support\Facades\DB;

class BordaService
{
    public function getAll()
    {
        return Borda::noCardapio()->orderBy('id')->get();
    }

    public function findById(int $id)
    {
        return Borda::noCardapio()->findOrFail($id);
    }

    public function store(array $data)
    {
        return DB::transaction(fn() => Borda::create($this->mapear($data)));
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $borda = Borda::findOrFail($id);
            $borda->update($this->mapear($data));
            return $borda;
        });
    }

    public function delete(int $id)
    {
        return DB::transaction(fn() => Borda::findOrFail($id)->delete());
    }

    private function mapear(array $data): array
    {
        foreach (['p', 'm', 'g'] as $tamanho) {
            if (array_key_exists($tamanho, $data)) {
                $data['preco_' . $tamanho] = $data[$tamanho];
                unset($data[$tamanho]);
            }
        }

        return $data;
    }
}
