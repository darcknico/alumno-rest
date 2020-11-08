<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSedeUsuarioTipoNotificacionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_sede_usuario_tipo_notificacion', function (Blueprint $table) {
            $table->bigIncrements('sut_id');
            $table->unsignedBigInteger('sun_id');
            $table->unsignedInteger('tsn_id');
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
        Schema::dropIfExists('tbl_sede_usuario_tipo_notificacion');
    }
}
