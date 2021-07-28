<?php

namespace App\Listeners;

use App\Functions\MateriaFunction;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ActualizarMateria
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
        if($event->id_materia>0){
            MateriaFunction::actualizarById($event->id_materia);
        }
    }
}
