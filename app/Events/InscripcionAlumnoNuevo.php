<?php

namespace App\Events;

use App\Models\Inscripcion;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class InscripcionAlumnoNuevo implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id_sede;
    public $id_inscripcion;
    public $id_alumno;
    public $id_plan_pago;
    public $id_carrera;
    public $inscripcion;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Inscripcion $inscripcion)
    {
        $this->id_sede = $inscripcion->id_sede;
        $this->id_inscripcion = $inscripcion->id;
        $this->id_alumno = $inscripcion->id_alumno;
        $this->id_plan_pago = $inscripcion->id_plan_pago;
        $this->id_carrera = $inscripcion->id_carrera;
        $this->inscripcion = Inscripcion::with('alumno','carrera')->find($inscripcion->id);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('sedes.'.$this->id_sede),
            new PrivateChannel('sedes.'.$this->id_sede.'.inscripciones'),
        ];
    }

    public function broadcastAs()
    {
        return 'inscripcion.alumno';
    }
}
