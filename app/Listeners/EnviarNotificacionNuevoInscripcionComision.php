<?php

namespace App\Listeners;

use App\Models\Alumno;
use App\Models\Comision;
use App\Models\ComisionAlumno;
use App\Mail\ComisionAlumnoInscripcionNuevo;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class EnviarNotificacionNuevoInscripcionComision implements ShouldQueue
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
            $comision = ComisionAlumno::find($event->id_comision_alumno);
            Mail::to($alumno)->send(new ComisionAlumnoInscripcionNuevo($comision));
        }
        return true;
    }
}
