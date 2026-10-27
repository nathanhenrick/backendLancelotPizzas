<?php

namespace App\Services;

use App\Models\Faixa;
use App\Models\Sabor;
use Illuminate\Support\Facades\DB;

class SaborService
{
    public function getAll()
    {
        return Sabor::noCardapio()->with('faixa')->orderBy('nome')->get();
    }

    public function findById(int $id)
    {
        return Sabor::noCardapio()->with('faixa')->findOrFail($id);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['tipo'] ??= 'salgada';
            $sabor = Sabor::create($this->mapear($data));
            $sabor->load('faixa');
            return $sabor;
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $sabor = Sabor::findOrFail($id);
            $sabor->update($this->mapear($data));
            $sabor->load('faixa');
            return $sabor;
        });
    }

    public function delete(int $id)
    {
        return DB::transaction(fn() => Sabor::findOrFail($id)->delete());
    }

    private function mapear(array $data): array
    {
        if (isset($data['faixa'])) {
            $data['faixa_id'] = Faixa::where('codigo', $data['faixa'])->value('id');
            unset($data['faixa']);
        }

        return $data;
    }
}
