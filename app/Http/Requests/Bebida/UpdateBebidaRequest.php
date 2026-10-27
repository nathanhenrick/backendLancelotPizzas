<?php

namespace App\Http\Requests\Bebida;

use App\Models\Bebida;
use App\Rules\LogoDataUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBebidaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'   => 'sometimes|string|max:80',
            'descricao' => 'sometimes|nullable|string|max:160',
            'preco'  => 'sometimes|numeric|min:0|max:9999',
            'modelo' => ['sometimes', 'nullable', Rule::in(Bebida::MODELOS)],
            'cor'    => 'sometimes|nullable|integer|between:0,16777215',
            'logo'   => ['sometimes', 'nullable', 'string', 'max:400000', new LogoDataUrl(Bebida::find($this->route('id'))?->logo)],
            'ativo'  => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'descricao.max' => 'A descrição pode ter no máximo :max caracteres.',
            'preco.numeric' => 'O preço deve ser um número.',
            'preco.min'     => 'O preço não pode ser negativo.',
            'modelo.in'     => 'Modelo 3D inválido.',
            'cor.between'   => 'A cor da bebida é inválida.',
            'logo.starts_with' => 'A logo precisa ser uma imagem.',
            'logo.max'      => 'A logo é grande demais. Use uma imagem menor.',
        ];
    }
}
