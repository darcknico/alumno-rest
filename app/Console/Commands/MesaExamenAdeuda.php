<?php

namespace App\Console\Commands;

use App\Models\Sede;
use App\Models\Mesa\MesaExamen;
use App\Models\Mesa\MesaExamenMateria;
use App\Models\Mesa\MesaExamenMateriaAlumno;

use App\Functions\MesaExamenFunction;
use Carbon\Carbon;

use Illuminate\Console\Command;

class MesaExamenAdeuda extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mesa_examen:adeuda';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar estado en las deudas de los alumnos inscriptos para la fecha actual';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $fecha = Carbon::now();
        $materias = MesaExamenMateria::whereHas('mesa_examen',function($q){
            $q->where('estado',1);
        })
        ->where('estado',1)
        ->whereDate('fecha','=',$fecha->toDateString())
        ->get();

        foreach ($materias as $materia) {
            $this->info('Materia: '.$materia->nombre);
            $materia = MesaExamenMateria::find($materia->id);
            MesaExamenFunction::actualizar_materia($materia,true);
        }
    }
}
