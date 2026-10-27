<?php

namespace App\Http\Requests\Pedido;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePedidoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status' => 'required|in:novo,preparo,saiu,concluido,cancelado',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Informe a situação do pedido.',
            'status.in'       => 'O campo :attribute deve ser um dos seguintes valores: :values.',
        ];
    }
}
