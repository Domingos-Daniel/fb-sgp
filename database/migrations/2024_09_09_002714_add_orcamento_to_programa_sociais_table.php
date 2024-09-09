<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrcamentoToProgramaSociaisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('programa_socials', function (Blueprint $table) {
            $table->decimal('orcamento', 15, 2)->default(0)->after('meta'); // Campo de orçamento
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('programa_socials', function (Blueprint $table) {
            $table->dropColumn('orcamento');
        });
    }
}
