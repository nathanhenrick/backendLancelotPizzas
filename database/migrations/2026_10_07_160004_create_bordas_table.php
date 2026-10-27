<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bordas', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 80);
            $table->decimal('preco_p', 8, 2)->default(0);
            $table->decimal('preco_m', 8, 2)->default(0);
            $table->decimal('preco_g', 8, 2)->default(0);
            $table->unsignedInteger('cor')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bordas');
    }
};
