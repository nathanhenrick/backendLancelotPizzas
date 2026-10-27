<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('configuracoes', function (Blueprint $table) {
            $table->string('razao_social', 120)->nullable();
            $table->string('cnpj', 14)->nullable();
            $table->text('descricao')->nullable();
            $table->string('email', 120)->nullable();
            $table->string('telefone', 11)->nullable();
            $table->string('instagram', 60)->nullable();
            $table->string('facebook', 100)->nullable();
            $table->string('cep', 8)->nullable();
            $table->string('logradouro', 120)->nullable();
            $table->string('numero', 15)->nullable();
            $table->string('complemento', 60)->nullable();
            $table->string('bairro', 80)->nullable();
            $table->string('cidade', 80)->nullable();
            $table->string('uf', 2)->nullable();
            $table->json('horarios')->nullable();
            $table->text('area_entrega')->nullable();
            $table->string('taxa_entrega', 120)->nullable();
            $table->string('tempo_entrega', 60)->nullable();
            $table->string('tempo_retirada', 60)->nullable();
            $table->text('formas_pagamento')->nullable();
            $table->string('lgpd_responsavel', 120)->nullable();
            $table->string('lgpd_email', 120)->nullable();
            $table->text('politica_privacidade')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('configuracoes', function (Blueprint $table) {
            $table->dropColumn([
                'razao_social', 'cnpj', 'descricao', 'email', 'telefone', 'instagram', 'facebook',
                'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf', 'horarios',
                'area_entrega', 'taxa_entrega', 'tempo_entrega', 'tempo_retirada', 'formas_pagamento',
                'lgpd_responsavel', 'lgpd_email', 'politica_privacidade',
            ]);
        });
    }
};
