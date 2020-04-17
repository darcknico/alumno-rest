<?php

namespace App\Events;

use App\Models\ComisionAlumno;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class InscripcionComisionModificado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $id_sede;
    public $id_inscripcion;
    public $id_alumno;
    public $id_comision_alumno;
    public $id_comision;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ComisionAlumno $item)
    {
        $this->id_sede = $item->comision->id_sede;
        $this->id_inscripcion = $item->id_inscripcion;
        $this->id_alumno = $item->id_alumno;
        $this->id_comision_alumno = $item->id;
        $this->id_comision = $item->id_comision;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('sedes.'.$this->id_sede.'.comisiones');
    }
}
