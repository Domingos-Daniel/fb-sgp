<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrcamentoGeraisTable extends Migration
{
    public function up()
    {
        Schema::create('orcamento_gerais', function (Blueprint $table) {
            $table->id();
            $table->decimal('valor_total', 15, 2);
            $table->string('ano_semestre'); // Ex: '2023-S1', '2023-S2'
            $table->enum('status', ['Pendente', 'Aprovado', 'Rejeitado'])->default('Pendente');
            $table->foreignId('id_criador')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orcamento_gerais');
    }
}
