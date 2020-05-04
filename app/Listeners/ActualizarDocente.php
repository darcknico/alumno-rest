<?php

namespace App\Listeners;

use App\Models\Academico\Docente;
use App\Functions\DocenteFunction;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ActualizarDocente
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
        $docente = Docente::find($event->id_usuario);
        DocenteFunction::actualizar($docente);
        return true;
    }
}
