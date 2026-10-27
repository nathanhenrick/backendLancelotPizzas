<?php

namespace Database\Seeders;

use App\Models\Adicional;
use App\Models\Bebida;
use App\Models\Borda;
use App\Models\Configuracao;
use App\Models\Esfirra;
use App\Models\Faixa;
use App\Models\Sabor;
use App\Models\Tamanho;
use Illuminate\Database\Seeder;

class CardapioSeeder extends Seeder
{
    private const TAMANHOS = [
        ['codigo' => 'p', 'nome' => 'Broto', 'info' => '25 cm · 4 fatias', 'max_sabores' => 2, 'escala' => 0.78],
        ['codigo' => 'm', 'nome' => 'Média', 'info' => '30 cm · 6 fatias', 'max_sabores' => 3, 'escala' => 0.89],
        ['codigo' => 'g', 'nome' => 'Grande', 'info' => '35 cm · 8 fatias', 'max_sabores' => 4, 'escala' => 1],
    ];

    private const FAIXAS = [
        ['codigo' => 'trad', 'nome' => 'Tradicional', 'preco_p' => 39, 'preco_m' => 54, 'preco_g' => 68],
        ['codigo' => 'esp', 'nome' => 'Especial', 'preco_p' => 45, 'preco_m' => 62, 'preco_g' => 78],
        ['codigo' => 'prem', 'nome' => 'Premium', 'preco_p' => 52, 'preco_m' => 72, 'preco_g' => 89],
    ];

    public function run(): void
    {
        $this->estrutura();
        $this->semear();
    }

    private function estrutura(): void
    {
        if (! Configuracao::exists()) {
            Configuracao::create(['nome' => 'Lancelot Pizzas', 'regra_preco' => 'maior']);
        }
        foreach (self::TAMANHOS as $tamanho) {
            Tamanho::firstOrCreate(['codigo' => $tamanho['codigo']], $tamanho);
        }
        foreach (self::FAIXAS as $faixa) {
            Faixa::firstOrCreate(['codigo' => $faixa['codigo']], $faixa);
        }
    }

    private function semear(): void
    {
        if (! Sabor::exists()) {
            $faixaId = Faixa::pluck('id', 'codigo');
            $sabores = [
                ['Calabresa', 'trad', 'Queijo, calabresa, cebola', ['calabresa', 'cebola']],
                ['Mussarela', 'trad', 'Queijo, tomate, orégano', ['mussarela', 'tomate', 'oregano']],
                ['Marguerita', 'trad', 'Queijo, tomate, manjericão', ['tomate', 'manjericao']],
                ['Milho com Bacon', 'trad', 'Queijo, milho, bacon', ['milho', 'bacon']],
                ['Frango com Catupiry', 'esp', 'Queijo, frango, catupiry', ['frango', 'catupiry']],
                ['Bacon', 'esp', 'Queijo, bacon', ['bacon']],
                ['Portuguesa', 'esp', 'Queijo, presunto, ovo, cebola, azeitona', ['presunto', 'ovo', 'cebola', 'azeitona']],
                ['Quatro Queijos', 'esp', 'Mussarela, catupiry, gorgonzola, parmesão', ['mussarela', 'catupiry', 'gorgonzola', 'parmesao']],
                ['Pepperoni', 'prem', 'Queijo, pepperoni, orégano', ['pepperoni', 'oregano']],
                ['Champignon', 'prem', 'Queijo, champignon, azeitona, orégano', ['champignon', 'azeitona', 'oregano']],
            ];
            foreach ($sabores as [$nome, $faixa, $descricao, $ingredientes]) {
                Sabor::create([
                    'tipo'         => 'salgada',
                    'faixa_id'     => $faixaId[$faixa],
                    'nome'         => $nome,
                    'descricao'    => $descricao,
                    'ingredientes' => $ingredientes,
                    'ativo'        => true,
                ]);
            }
        }

        if (! Sabor::where('tipo', 'doce')->exists()) {
            $faixaId = Faixa::pluck('id', 'codigo');
            $doces = [
                ['Brigadeiro', 'esp', 'Chocolate e granulado', ['chocolate', 'granulado']],
                ['Banana com Canela', 'trad', 'Banana, canela e açúcar', ['banana', 'canela']],
                ['Chocolate com Morango', 'prem', 'Chocolate ao leite e morango', ['chocolate', 'morango']],
            ];
            foreach ($doces as [$nome, $faixa, $descricao, $ingredientes]) {
                Sabor::firstOrCreate(['nome' => $nome], [
                    'tipo'         => 'doce',
                    'faixa_id'     => $faixaId[$faixa],
                    'descricao'    => $descricao,
                    'ingredientes' => $ingredientes,
                    'ativo'        => true,
                ]);
            }
        }

        if (! Borda::exists()) {
            Borda::create(['nome' => 'Tradicional', 'preco_p' => 0, 'preco_m' => 0, 'preco_g' => 0, 'cor' => null, 'ativo' => true]);
            Borda::create(['nome' => 'Recheada com catupiry', 'preco_p' => 8, 'preco_m' => 10, 'preco_g' => 12, 'cor' => 0xFFF3D2, 'ativo' => true]);
            Borda::create(['nome' => 'Recheada com cheddar', 'preco_p' => 8, 'preco_m' => 10, 'preco_g' => 12, 'cor' => 0xF2A02A, 'ativo' => true]);
        }

        if (! Bebida::exists()) {
            Bebida::create(['nome' => 'Refrigerante 2 L', 'descricao' => 'Cola, guaraná ou laranja', 'preco' => 14, 'modelo' => 'garrafa_2l', 'cor' => 0x3B1A0E, 'ativo' => true]);
            Bebida::create(['nome' => 'Refrigerante lata', 'descricao' => 'Lata de 350 ml, bem gelada', 'preco' => 6, 'modelo' => 'lata', 'cor' => 0x3B1A0E, 'ativo' => true]);
            Bebida::create(['nome' => 'Suco 1 L', 'descricao' => 'Laranja natural, na jarra', 'preco' => 12, 'modelo' => 'jarra', 'cor' => 0xF28C28, 'ativo' => true]);
            Bebida::create(['nome' => 'Água 500 ml', 'descricao' => 'Com ou sem gás', 'preco' => 4, 'modelo' => 'garrafa_500', 'cor' => 0xD9EEF7, 'ativo' => true]);
            Bebida::create(['nome' => 'Suco copo 300 ml', 'descricao' => 'Laranja natural, no copo', 'preco' => 7, 'modelo' => 'copo', 'cor' => 0xF28C28, 'ativo' => true]);
        }

        if (! Bebida::where('modelo', 'long_neck')->exists()) {
            $logo = fn (string $texto, string $fundo) => 'data:image/svg+xml;base64,' . base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="320" height="200"><rect width="320" height="200" rx="28" fill="' . $fundo . '"/>'
                . '<text x="160" y="118" font-family="Arial, sans-serif" font-size="56" font-weight="bold" fill="#fff" text-anchor="middle">' . $texto . '</text></svg>'
            );

            Bebida::create(['nome' => 'Long neck Pilsen', 'descricao' => 'Cerveja pilsen 330 ml', 'preco' => 9, 'modelo' => 'long_neck', 'cor' => 0x2F6B2F, 'logo' => $logo('PILSEN', '#1f6b3a'), 'ativo' => true]);
            Bebida::create(['nome' => 'Long neck Premium', 'descricao' => 'Cerveja premium 330 ml', 'preco' => 12, 'modelo' => 'long_neck', 'cor' => 0x7A3D0C, 'logo' => $logo('PREMIUM', '#8a1f1f'), 'ativo' => true]);
        }

        if (! Esfirra::exists()) {
            Esfirra::create(['nome' => 'Esfirra de carne', 'descricao' => 'Carne temperada, aberta', 'preco' => 5, 'tamanhos' => [['nome' => 'Pequena', 'preco' => 5], ['nome' => 'Grande', 'preco' => 9]], 'ingredientes' => ['tomate', 'cebola'], 'cor' => 0x8C3B22, 'ativo' => true]);
            Esfirra::create(['nome' => 'Esfirra de queijo', 'descricao' => 'Mussarela derretida', 'preco' => 5, 'tamanhos' => [['nome' => 'Pequena', 'preco' => 5], ['nome' => 'Grande', 'preco' => 9]], 'ingredientes' => ['mussarela'], 'cor' => 0xF2C75C, 'ativo' => true]);
            Esfirra::create(['nome' => 'Esfirra de chocolate', 'tipo' => 'doce', 'descricao' => 'Chocolate ao leite', 'preco' => 6, 'ingredientes' => ['chocolate'], 'cor' => 0x5A3220, 'ativo' => true]);
            Esfirra::create(['nome' => 'Esfirra de frango com catupiry', 'descricao' => 'Frango desfiado e catupiry', 'preco' => 6, 'ingredientes' => ['frango', 'catupiry'], 'cor' => 0xE6CFA0, 'ativo' => true]);
        }

        if (! Adicional::exists()) {
            foreach ([['Bacon', 5], ['Cheddar', 4], ['Catupiry', 6], ['Mussarela extra', 8], ['Milho', 2], ['Ovo', 2], ['Palmito', 5]] as [$nome, $preco]) {
                Adicional::create(['nome' => $nome, 'preco' => $preco, 'ativo' => true]);
            }
        }

        $ids = Adicional::pluck('id')->all();
        Sabor::whereNull('adicionais')->get()->each(fn (Sabor $sabor) => $sabor->update(['adicionais' => $ids]));
        Esfirra::where('tipo', 'salgada')->whereNull('adicionais')->get()->each(fn (Esfirra $esfirra) => $esfirra->update(['adicionais' => $ids]));
    }
}
