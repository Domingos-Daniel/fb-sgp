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
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_patrocinio');
            $table->date('data_pagamento');
            $table->decimal('valor', 15, 2);
            $table->string('status')->default('pendente');
            $table->dateTime('data_aprovacao')->nullable();
            $table->text('motivo_rejeicao')->nullable();
            $table->unsignedBigInteger('id_aprovador')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->foreign('id_patrocinio')->references('id')->on('patrocinios')->onDelete('cascade');
            $table->foreign('id_aprovador')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
