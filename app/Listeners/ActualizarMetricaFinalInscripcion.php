<?php

namespace App\Listeners;
use App\Models\Inscripcion;

use App\Functions\InscripcionFunction;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ActualizarMetricaFinalInscripcion
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

        $inscripcion->porcentaje_aprobados = InscripcionFunction::obtenerPorcentajeAprobado($inscripcion);
        $todos = InscripcionFunction::obtenerTPFinal($inscripcion);
        $aprobados = InscripcionFunction::obtenerTPFinalAprobados($inscripcion);
        $inscripcion->final_total = $todos['total'];
        $inscripcion->final_total_aprobados = $aprobados['total'];
        $inscripcion->final_promedio = $todos['promedio'];
        $inscripcion->final_promedio_aprobados = $aprobados['promedio'];
        
        $inscripcion->save();

        return true;
    }
}
