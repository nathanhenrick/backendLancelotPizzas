<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('politica_privacidade', function (Blueprint $table) {
            $table->id();
            $table->longText('texto')->nullable();
            $table->string('responsavel', 120)->nullable();
            $table->string('email', 120)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('politica_privacidade');
    }
};
