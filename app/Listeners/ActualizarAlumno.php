<?php

namespace App\Listeners;

use App\Models\Alumno;
use App\Models\Inscripcion;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ActualizarAlumno
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $alumno = Alumno::find($event->id_alumno);
        $inscripcion = Inscripcion::where('estado',1)
        ->where('id_alumno',$event->id_alumno)
        ->first();
        if($inscripcion){
            $alumno->id_tipo_alumno_estado = 2;
        } else {
            $alumno->id_tipo_alumno_estado = 1;
        }
        $alumno->save();
        return true;
    }
}
