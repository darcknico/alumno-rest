<?php

namespace App\Listeners;

use App\Models\Comision;
use App\Models\ComisionAlumno;

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
        $alumnos_cantidad = ComisionAlumno::selectRaw('count(*) as total')
            ->where([
                'estado' => 1,
                'com_id' => $event->id_comision,
            ])->groupBy('com_id')->first();
        $comision->alumnos_cantidad = $alumnos_cantidad->total??0;
        $comision->save();

        return true;
    }
}
