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
        Schema::create('patrocinios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_beneficiario');
            $table->unsignedBigInteger('id_subprograma');
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->string('status')->default('ativo');
            $table->text('observacoes')->nullable();
            $table->unsignedBigInteger('id_criador');
            $table->timestamps();

            $table->foreign('id_beneficiario')->references('id')->on('beneficiarios')->onDelete('cascade');
            $table->foreign('id_subprograma')->references('id')->on('subprogramas')->onDelete('cascade');
            $table->foreign('id_criador')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patrocinios');
    }
};
