<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sabores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faixa_id');
            $table->string('nome', 80);
            $table->string('descricao')->nullable();
            $table->json('ingredientes');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->foreign('faixa_id')->references('id')->on('faixas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sabores');
    }
};
