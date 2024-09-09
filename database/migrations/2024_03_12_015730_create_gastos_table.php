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
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_programa');
            $table->unsignedBigInteger('id_subprograma');
            $table->decimal('valor_gasto', 10, 2);
            $table->foreign('id_programa')->references('id')->on('programas_socials');
            $table->foreign('id_subprograma')->references('id')->on('subprogramas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
