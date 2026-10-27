<?php

namespace App\Http\Requests\Pedido;

use Illuminate\Validation\Rule;
use App\Models\Adicional;
use App\Models\Bebida;
use App\Models\Borda;
use App\Models\Esfirra;
use App\Models\Sabor;
use App\Models\Tamanho;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePedidoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $listas = [];
        foreach (['sabores', 'ajustes'] as $campo) {
            $valor = $this->input($campo);
            if (is_array($valor)) {
                ksort($valor);
                $listas[$campo] = array_values($valor);
            }
        }

        $this->merge($listas);
    }

    public function rules(): array
    {
        return [
            'cliente'               => 'nullable|string|max:80',
            'modo'                  => 'required|in:entrega,retirada',
            'tamanho'               => ['required_with:sabores', 'nullable', 'string', Rule::exists('tamanhos', 'codigo')],
            'sabores'               => 'sometimes|array|max:4',
            'sabores.*'             => ['required', 'integer', Rule::exists('sabores', 'id')],
            'borda'                 => ['nullable', 'integer', Rule::exists('bordas', 'id')],
            'bebidas'               => 'sometimes|array|max:20',
            'bebidas.*.id'          => ['required', 'integer', 'distinct', Rule::exists('bebidas', 'id')],
            'bebidas.*.quantidade'  => 'required|integer|min:1|max:20',
            'esfirras'              => 'sometimes|array|max:20',
            'esfirras.*.id'         => ['required', 'integer', Rule::exists('esfirras', 'id')],
            'esfirras.*.tamanho'    => 'nullable|string|max:30',
            'esfirras.*.quantidade' => 'required|integer|min:1|max:50',
            'esfirras.*.ajuste'                => 'sometimes|array',
            'esfirras.*.ajuste.removidos'      => 'sometimes|array|max:20',
            'esfirras.*.ajuste.removidos.*'    => 'string|max:40',
            'esfirras.*.ajuste.adicionais'     => 'sometimes|array|max:10',
            'esfirras.*.ajuste.adicionais.*'   => ['integer', 'distinct', Rule::exists('adicionais', 'id')],
            'esfirras.*.ajuste.observacao'     => 'nullable|string|max:140',
            'ajustes'                  => 'sometimes|array|max:4',
            'ajustes.*.removidos'      => 'sometimes|array|max:20',
            'ajustes.*.removidos.*'    => 'string|max:40',
            'ajustes.*.adicionais'     => 'sometimes|array|max:10',
            'ajustes.*.adicionais.*'   => ['integer', 'distinct', Rule::exists('adicionais', 'id')],
            'ajustes.*.observacao'     => 'nullable|string|max:140',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if (! $this->input('sabores') && ! $this->input('esfirras')) {
                    $validator->errors()->add('sabores', 'Escolha uma pizza ou ao menos uma esfirra.');
                    return;
                }

                $tamanho = Tamanho::where('codigo', $this->input('tamanho'))->first();
                if ($tamanho && count($this->input('sabores', [])) > $tamanho->max_sabores) {
                    $validator->errors()->add('sabores', "A pizza {$tamanho->nome} aceita até {$tamanho->max_sabores} sabores.");
                }

                $ids = array_unique($this->input('sabores', []));
                if (Sabor::whereIn('id', $ids)->where('ativo', true)->count() !== count($ids)) {
                    $validator->errors()->add('sabores', 'Algum sabor escolhido saiu do cardápio. Atualize a página e escolha de novo.');
                }

                $borda = $this->input('borda');
                if ($borda && ! Borda::whereKey($borda)->where('ativo', true)->exists()) {
                    $validator->errors()->add('borda', 'A borda escolhida saiu do cardápio.');
                }

                if (count($this->input('ajustes', [])) > count($this->input('sabores', []))) {
                    $validator->errors()->add('ajustes', 'Há mais personalizações do que sabores.');
                }

                $ordem = array_values($this->input('sabores', []));
                foreach (array_values($this->input('ajustes', [])) as $i => $ajuste) {
                    $permitidos = Sabor::whereKey($ordem[$i] ?? 0)->value('adicionais') ?? [];
                    if (array_diff($ajuste['adicionais'] ?? [], array_map('intval', $permitidos))) {
                        $validator->errors()->add('ajustes', 'Algum adicional não é oferecido para esse sabor.');
                    }
                }

                $extras = collect($this->input('ajustes', []))->pluck('adicionais')->flatten()->unique()->filter();
                if ($extras->isNotEmpty() && Adicional::whereIn('id', $extras)->where('ativo', true)->count() !== $extras->count()) {
                    $validator->errors()->add('ajustes', 'Algum adicional escolhido saiu do cardápio.');
                }

                $esfirras = array_unique(array_column($this->input('esfirras', []), 'id'));
                if ($esfirras && Esfirra::whereIn('id', $esfirras)->where('ativo', true)->count() !== count($esfirras)) {
                    $validator->errors()->add('esfirras', 'Alguma esfirra escolhida saiu do cardápio.');
                }

                $extrasEsfirras = collect($this->input('esfirras', []))->pluck('ajuste.adicionais')->flatten()->unique()->filter();
                if ($extrasEsfirras->isNotEmpty() && Adicional::whereIn('id', $extrasEsfirras)->where('ativo', true)->count() !== $extrasEsfirras->count()) {
                    $validator->errors()->add('esfirras', 'Algum adicional escolhido saiu do cardápio.');
                }

                foreach ($this->input('esfirras', []) as $item) {
                    $extras = $item['ajuste']['adicionais'] ?? [];
                    if ($extras) {
                        $alvo = Esfirra::find($item['id']);
                        $permitidos = array_map('intval', $alvo?->adicionais ?? []);
                        if (! $alvo || $alvo->tipo === 'doce' || array_diff($extras, $permitidos)) {
                            $validator->errors()->add('esfirras', 'Algum adicional não é oferecido para essa esfirra.');
                        }
                    }
                    $tamanho = $item['tamanho'] ?? null;
                    $esfirra = $tamanho ? Esfirra::find($item['id']) : null;
                    if ($esfirra && ! collect($esfirra->tamanhosComPreco())->contains('nome', $tamanho)) {
                        $validator->errors()->add('esfirras', "A esfirra {$esfirra->nome} não tem o tamanho {$tamanho}.");
                    }
                }

                $bebidas = array_column($this->input('bebidas', []), 'id');
                if ($bebidas && Bebida::whereIn('id', $bebidas)->where('ativo', true)->count() !== count($bebidas)) {
                    $validator->errors()->add('bebidas', 'Alguma bebida escolhida saiu do cardápio.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'required'        => 'O campo :attribute é obrigatório.',
            'modo.in'         => 'Escolha entrega ou retirada.',
            'tamanho.exists'  => 'Tamanho inválido.',
                        'sabores.max'     => 'A pizza aceita no máximo :max sabores.',
            'sabores.*.exists'=> 'Sabor inválido.',
            'borda.exists'    => 'Borda inválida.',
            'bebidas.*.exists'=> 'Bebida inválida.',
            'esfirras.*.exists' => 'Esfirra inválida.',
            'ajustes.*.adicionais.*.exists' => 'Adicional inválido.',
            'max'             => 'O campo :attribute não pode ser maior que :max.',
        ];
    }
}
