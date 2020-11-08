<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInscripcionEstadoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_inscripcion_estado', function (Blueprint $table) {
            $table->bigIncrements('ies_id');
            $table->unsignedBigInteger('ins_id');
            $table->unsignedInteger('tie_id');
            $table->unsignedInteger('tie_id_tipo_inscripcion_estado');
            $table->date('ies_fecha');
            $table->text('ies_observaciones')->nullable();
            $table->unsignedBigInteger('usu_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_inscripcion_estado');
    }
}
