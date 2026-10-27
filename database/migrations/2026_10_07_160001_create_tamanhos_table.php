<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tamanhos', function (Blueprint $table) {
            $table->id();
            $table->enum('codigo', ['p', 'm', 'g'])->unique();
            $table->string('nome', 40);
            $table->string('info', 80)->nullable();
            $table->unsignedTinyInteger('max_sabores');
            $table->decimal('escala', 4, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tamanhos');
    }
};
