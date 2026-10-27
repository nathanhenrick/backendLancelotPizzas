<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bebidas', function (Blueprint $table) {
            $table->string('descricao', 160)->nullable()->after('nome');
        });
    }

    public function down(): void
    {
        Schema::table('bebidas', function (Blueprint $table) {
            $table->dropColumn('descricao');
        });
    }
};
