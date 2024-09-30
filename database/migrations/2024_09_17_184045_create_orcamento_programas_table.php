<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrcamentoProgramasTable extends Migration
{
    public function up()
    {
        Schema::create('orcamento_programas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_programa')->constrained('programa_socials')->onDelete('cascade');
            $table->foreignId('id_orcamento')->constrained('orcamento_gerais')->onDelete('cascade');
            $table->decimal('valor', 15, 2);
            $table->enum('status', ['Pendente', 'Aprovado', 'Rejeitado'])->default('Pendente');
            $table->foreignId('id_criador')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orcamento_programas');
    }
}
