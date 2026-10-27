<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('cliente', 80)->nullable();
            $table->enum('modo', ['entrega', 'retirada']);
            $table->enum('status', ['novo', 'preparo', 'saiu', 'concluido', 'cancelado'])->default('novo');
            $table->string('tamanho', 40);
            $table->json('sabores');
            $table->decimal('valor_pizza', 10, 2);
            $table->decimal('valor_borda', 10, 2)->default(0);
            $table->decimal('valor_bebidas', 10, 2)->default(0);
            $table->decimal('valor_total', 10, 2);
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('pedido_itens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->string('titulo', 120);
            $table->json('detalhes');
            $table->decimal('valor', 10, 2);
            $table->timestamps();

            $table->foreign('pedido_id')->references('id')->on('pedidos')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_itens');
        Schema::dropIfExists('pedidos');
    }
};
