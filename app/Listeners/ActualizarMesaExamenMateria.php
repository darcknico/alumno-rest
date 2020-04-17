<?php

namespace App\Listeners;

use App\Models\Mesa\MesaExamenMateria;
use App\Functions\MesaExamenFunction;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ActualizarMesaExamenMateria
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
        $materia = MesaExamenMateria::find($event->id_mesa_examen_materia);
        MesaExamenFunction::actualizar_materia($materia);
        
        return true;
    }
}
