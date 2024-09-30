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
        Schema::table('subprogramas', function (Blueprint $table) {
            $table->string('tipo_pagamento')->nullable()->after('valor'); // e.g., 'trimestral', 'anual'
            //$table->decimal('valor_pagamento', 15, 2)->nullable()->after('tipo_pagamento');
            $table->integer('duracao_patrocinio')->nullable(); // em meses
            $table->json('regras_especificas')->nullable()->after('duracao_patrocinio');
        });
    }

    public function down(): void
    {
        Schema::table('subprogramas', function (Blueprint $table) {
            $table->dropColumn(['tipo_pagamento', 'duracao_patrocinio', 'regras_especificas']);
        });
    }
};
