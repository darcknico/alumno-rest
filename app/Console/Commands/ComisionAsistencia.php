<?php

namespace App\Console\Commands;

use App\Models\Sede;
use App\Models\Comision;
use App\Models\Asistencia;
use App\Models\AsistenciaAlumno;
use App\Models\Comision\Horario;

use Carbon\Carbon;

use Illuminate\Console\Command;

class ComisionAsistencia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comision:asistencia';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera las asistencias de las comisiones correspondientes al dia';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    // dayOfWeek returns a number between 0 (sunday) and 6 (saturday)

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ahora = Carbon::now();
        $dayOfWeek = $ahora->dayOfWeek + 1;
        $registros = Horario::where('estado',1)
        ->where('id_dia',$dayOfWeek)
        ->where('asistencia',true)
        ->whereHas('comision',function($q)use($ahora){
            $q->where('estado',1)
            ->where('asistencia',true)
            ->where('anio',$ahora->year)
            ->whereNotNull('clase_inicio')
            ->whereNotNull('clase_final')
            ->whereDate('clase_inicio','>=',$ahora)
            ->whereDate('clase_final','<=',$ahora)
            ->whereHas('sede',function($qt){
                $qt->where('estado',1);
            });
        })
        ->get();

        foreach ($registros as $registro) {
            $asistencia = Asistencia::where('id_comision',$id_comision)
            ->where('estado',1)
            ->whereDate('fecha','=',$ahora)
            ->first();
            if(!$asistencia){
                $asistencia = new Asistencia;
                $asistencia->fecha = $ahora;
                $asistencia->alumnos_cantidad = $registro->comision->alumnos_cantidad;
                $asistencia->id_comision = $registro->id_comision;
                $asistencia->save();

                $asistentes = ComisionAlumno::where([
                    'com_id' => $registro->id_comision,
                    'estado' => 1,
                ])->pluck('alu_id')->toArray();

                foreach ($asistentes as $asistente) {
                    $alumno = new AsistenciaAlumno;
                    $alumno->id_asistencia = $asistencia->id;
                    $alumno->id_alumno = $asistente;
                    $alumno->save();
                }
            }
        }
    }
}
