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
        Schema::create('beneficiarios', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_beneficiario')->default('Individual'); // Individual ou Institucional
            $table->string('nome');
            $table->string('bi')->nullable()->default('Não Aplicável');
            $table->string('nif')->nullable()->default('Não Aplicável');
            $table->date('data_nascimento')->nullable()->default(null);
            $table->string('genero')->nullable()->default('Não Aplicável');
            $table->string('email')->nullable();
            $table->string('telemovel')->nullable();
            $table->string('telemovel_alternativo')->nullable();
            $table->string('endereco')->nullable()->default('Não Aplicável');
            $table->string('pais')->nullable()->default('Não Aplicável');
            $table->string('coordenadas_bancarias')->nullable()->default('Não Aplicável');
            $table->string('ano_frequencia')->nullable()->default('Não Aplicável');
            $table->string('curso')->nullable()->default('Não Aplicável');
            $table->string('universidade_ou_escola')->nullable()->default('Não Aplicável');
            $table->text('observacoes')->nullable();
            $table->foreignId('id_criador')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiarios');
    }
};
