<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programa_socials', function (Blueprint $table) {
            // $table->unsignedBigInteger('id_orcamento')->nullable()->after('meta'); // Chave estrangeira para orçamento geral

            // $table->foreign('id_orcamento')->references('id')->on('orcamento_gerais')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('programa_socials', function (Blueprint $table) {
            $table->dropForeign(['id_orcamento']);
            $table->dropColumn('id_orcamento');
        });
    }
};
