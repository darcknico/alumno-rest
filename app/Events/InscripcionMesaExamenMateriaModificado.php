<?php

namespace App\Events;

use App\Models\Mesa\MesaExamenMateriaAlumno;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class InscripcionMesaExamenMateriaModificado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $id_sede;
    public $id_mesa_examen;
    public $id_mesa_examen_materia;
    public $id_mesa_examen_materia_alumno;
    public $id_inscripcion;
    public $id_alumno;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(MesaExamenMateriaAlumno $item)
    {
        $this->id_sede = $item->mesa_examen_materia->mesa_examen->id_sede;
        $this->id_mesa_examen = $item->mesa_examen_materia->id_mesa_examen;
        $this->id_mesa_examen_materia = $item->id_mesa_examen_materia;
        $this->id_mesa_examen_materia_alumno = $item->id;
        $this->id_inscripcion = $item->id_inscripcion;
        $this->id_alumno = $item->id_alumno;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('sedes.'.$this->id_sede.'.mesas');
    }
}
