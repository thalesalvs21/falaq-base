<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moderacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pergunta_id')->constrained()->cascadeOnDelete();
            $table->text('motivo');
            $table->string('status')->default('analisando');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderacoes');
    }
};
