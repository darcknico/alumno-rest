<?php

use Illuminate\Database\Seeder;

class TipoDocenteEstadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tbl_tipo_docente_estado')->insert([
            [
            	'tde_nombre' => 'Inactivo',
            	'tde_descripcion' => 'El docente se encuentra inhabilitado.',
            ],
            [
            	'tde_nombre' => 'Activo',
            	'tde_descripcion' => 'El docente se encuentra activo para realizar tareas.',
            ],
            [
            	'tde_nombre' => 'Jubilado',
            	'tde_descripcion' => 'La jubilacion del docente a partir de una fecha.',
            ],
            [
            	'tde_nombre' => 'Licencia',
            	'tde_descripcion' => 'El docente se encuentra en licencia por una fecha determinada, y dado al archivo adjunto.',
            ]
        ]);
    }
}
