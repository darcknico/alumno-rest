<?php

namespace App\Listeners;

use App\Models\Alumno;
use App\Models\Mesa\MesaExamenMateriaAlumno;
use App\Mail\MesaExamenMateriaAlumnoInscripcionNuevo;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class EnviarNotificacionNuevoInscripcionMesaExamenMateria implements ShouldQueue
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
        if($alumno->email){
            $mesa = MesaExamenMateriaAlumno::find($event->id_mesa_examen_materia_alumno);
            Mail::to($alumno)->send(new MesaExamenMateriaAlumnoInscripcionNuevo($mesa));
        }
        return true;
    }
}
