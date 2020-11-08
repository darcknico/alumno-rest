<?php

use Illuminate\Database\Seeder;

class TipoSedeNotificacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tbl_tipo_sede_notificacion')->insert([
            [
            	'tns_nombre' => 'Pago MercadoPago',
            	'tns_codigo' => 'MP',
            	'tns_descripcion' => 'Envio por correo de los pagos realizados con MercadoPago',
            ],
        ]);
    }
}
