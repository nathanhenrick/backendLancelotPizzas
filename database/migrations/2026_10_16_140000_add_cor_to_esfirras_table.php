<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('esfirras', function (Blueprint $table) {
            $table->unsignedInteger('cor')->nullable()->after('preco');
        });
    }

    public function down(): void
    {
        Schema::table('esfirras', fn (Blueprint $t) => $t->dropColumn('cor'));
    }
};
