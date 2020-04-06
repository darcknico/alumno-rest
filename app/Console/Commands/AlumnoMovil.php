<?php

namespace App\Console\Commands;

use App\Models\Alumno;

use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;

class AlumnoMovil extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alumno:movil';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inicia a los alumnos sin contraseña, asignando como contraseña su numero de documento';

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
        $cantidad = Alumno::where('estado',1)
            ->whereNotNull('documento')
            ->whereNull('alu_password')
            ->count();
        Log::info('Cantidad de alumnos a generar: '.$cantidad);
        $iteracion = 1;
        Alumno::where('estado',1)
            ->whereNotNull('documento')
            ->whereNull('alu_password')
            ->chunk(100,function($alumnos)use(&$iteracion){
                foreach ($alumnos as $alumno) {
                    $alumno->alu_password = bcrypt($alumno->documento);
                    $alumno->save();
                }
                Log::info('Iteracion: '.$iteracion);
                $iteracion++;
            });
    }
}
