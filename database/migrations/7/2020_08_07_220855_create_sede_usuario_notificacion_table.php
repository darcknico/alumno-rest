<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSedeUsuarioNotificacionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_sede_usuario_notificacion', function (Blueprint $table) {
            $table->bigIncrements('sun_id');
            $table->unsignedInteger('usu_id');
            $table->unsignedInteger('sed_id');
            $table->string('sun_email');
            $table->string('sun_activo');
            $table->timestamps();
            $table->boolean('estado')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_sede_usuario_notificacion');
    }
}
