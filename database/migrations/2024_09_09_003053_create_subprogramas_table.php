<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subprogramas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_programa')->constrained('programa_socials')->onDelete('cascade')->onUpdate('cascade'); // Chave estrangeira
            $table->text('descricao');
            $table->decimal('valor', 15, 2)->default(0);
            $table->foreignId('id_criador')->constrained('users')->onDelete('cascade'); // Criador
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subprogramas');
    }
};
