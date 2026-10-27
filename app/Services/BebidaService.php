<?php

namespace App\Services;

use App\Models\Bebida;
use Illuminate\Support\Facades\DB;

class BebidaService
{
    public function getAll()
    {
        return Bebida::noCardapio()->orderBy('id')->get();
    }

    public function findById(int $id)
    {
        return Bebida::noCardapio()->findOrFail($id);
    }

    public function store(array $data)
    {
        return DB::transaction(fn() => Bebida::create($data));
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $bebida = Bebida::findOrFail($id);
            $bebida->update($data);
            return $bebida;
        });
    }

    public function delete(int $id)
    {
        return DB::transaction(fn() => Bebida::findOrFail($id)->delete());
    }
}
