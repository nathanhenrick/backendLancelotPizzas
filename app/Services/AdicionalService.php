<?php

namespace App\Services;

use App\Models\Adicional;
use Illuminate\Support\Facades\DB;

class AdicionalService
{
    public function getAll()
    {
        return Adicional::noCardapio()->orderBy('id')->get();
    }

    public function findById(int $id)
    {
        return Adicional::noCardapio()->findOrFail($id);
    }

    public function store(array $data)
    {
        return DB::transaction(fn() => Adicional::create($data));
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $adicional = Adicional::findOrFail($id);
            $adicional->update($data);
            return $adicional;
        });
    }

    public function delete(int $id)
    {
        return DB::transaction(fn() => Adicional::findOrFail($id)->delete());
    }
}
