<?php

namespace App\Listeners;

use App\Models\Comision;
use App\Models\ComisionAlumno;
use App\Functions\ComisionFunction;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ActualizarComision
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
        $comision = Comision::find($event->id_comision);
        ComisionFunction::actualizar($comision);

        return true;
    }
}
