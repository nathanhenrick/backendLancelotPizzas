<?php

namespace App\Http\Requests\Bebida;

use App\Models\Bebida;
use App\Rules\LogoDataUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBebidaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nome'   => 'required|string|max:80',
            'descricao' => 'nullable|string|max:160',
            'preco'  => 'required|numeric|min:0|max:9999',
            'modelo' => ['nullable', Rule::in(Bebida::MODELOS)],
            'cor'    => 'nullable|integer|between:0,16777215',
            'logo'   => ['nullable', 'string', 'max:400000', new LogoDataUrl],
            'ativo'  => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'required'      => 'O campo :attribute é obrigatório.',
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
