<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblDocenteEstadoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_docente_estado', function (Blueprint $table) {
            $table->bigIncrements('des_id');
            $table->integer('usu_id')->comment('Identificación del docente');
            $table->unsignedInteger('tde_id');
            $table->date('des_fecha_inicial')->nullable();
            $table->date('des_fecha_final')->nullable();
            $table->text('des_observaciones')->nullable();
            $table->string('des_archivo')->nullable()->comment('Nombre del archivo');
            $table->string('des_dir')->nullable()->comment('Direccion fisica del archivo');
            $table->timestamps();

            $table->foreign('tde_id')->references('tde_id')->on('tbl_tipo_docente_estado')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_docente_estado');
    }
}
