<?php

namespace App\Services;

use App\Models\Esfirra;
use Illuminate\Support\Facades\DB;

class EsfirraService
{
    public function getAll()
    {
        return Esfirra::noCardapio()->orderBy('id')->get();
    }

    public function findById(int $id)
    {
        return Esfirra::noCardapio()->findOrFail($id);
    }

    public function store(array $data)
    {
        $data['tipo'] ??= 'salgada';

        return DB::transaction(fn() => Esfirra::create($data));
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $esfirra = Esfirra::findOrFail($id);
            $esfirra->update($data);
            return $esfirra;
        });
    }

    public function delete(int $id)
    {
        return DB::transaction(fn() => Esfirra::findOrFail($id)->delete());
    }
}
