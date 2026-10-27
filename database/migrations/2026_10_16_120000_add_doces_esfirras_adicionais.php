<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sabores', function (Blueprint $table) {
            $table->string('tipo', 10)->default('salgada')->after('nome');
        });

        Schema::table('bebidas', function (Blueprint $table) {
            $table->mediumText('logo')->nullable()->after('cor');
        });

        Schema::create('esfirras', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 80);
            $table->string('descricao', 160)->nullable();
            $table->decimal('preco', 8, 2);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('adicionais', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 60);
            $table->decimal('preco', 8, 2);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->decimal('valor_esfirras', 10, 2)->default(0)->after('valor_bebidas');
            $table->decimal('valor_adicionais', 10, 2)->default(0)->after('valor_esfirras');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', fn (Blueprint $t) => $t->dropColumn(['valor_esfirras', 'valor_adicionais']));
        Schema::dropIfExists('adicionais');
        Schema::dropIfExists('esfirras');
        Schema::table('bebidas', fn (Blueprint $t) => $t->dropColumn('logo'));
        Schema::table('sabores', fn (Blueprint $t) => $t->dropColumn('tipo'));
    }
};
