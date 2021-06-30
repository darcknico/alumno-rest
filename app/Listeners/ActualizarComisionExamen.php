<?php

namespace App\Listeners;

use App\Functions\ComisionFunction;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ActualizarComisionExamen
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
        
        ComisionFunction::examenActualizarById($event->id_comision_examen);

        return true;
    }
}
