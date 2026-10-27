<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bebidas', function (Blueprint $table) {
            $table->string('modelo', 20)->nullable()->after('preco');
            $table->unsignedInteger('cor')->nullable()->after('modelo');
        });
    }

    public function down(): void
    {
        Schema::table('bebidas', function (Blueprint $table) {
            $table->dropColumn(['modelo', 'cor']);
        });
    }
};
