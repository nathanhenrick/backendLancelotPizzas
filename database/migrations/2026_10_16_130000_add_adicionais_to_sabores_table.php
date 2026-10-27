<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sabores', function (Blueprint $table) {
            $table->json('adicionais')->nullable()->after('ingredientes');
        });
    }

    public function down(): void
    {
        Schema::table('sabores', fn (Blueprint $t) => $t->dropColumn('adicionais'));
    }
};
