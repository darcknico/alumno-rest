<?php

namespace App\Listeners;

use App\Models\Inscripcion;

use App\Functions\InscripcionFunction;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ActualizarPeriodoLectivoInscripcion
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
        $inscripcion = Inscripcion::find($event->id_inscripcion);
        $inscripcion->id_periodo_lectivo = InscripcionFunction::obtenerIdPeriodoLectivo($inscripcion);
        $inscripcion->save();

        return true;
    }
}
