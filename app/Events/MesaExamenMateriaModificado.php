<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MesaExamenMateriaModificado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id_materia;
    public $id_carrera;
    public $id_mesa_examen_materia;
    public $id_mesa_examen;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($mesaExamenMateria)
    {
        $this->id_materia = $mesaExamenMateria->id_materia;
        $this->id_carrera = $mesaExamenMateria->id_carrera;
        $this->id_mesa_examen_materia = $mesaExamenMateria->id;
        $this->id_mesa_examen = $mesaExamenMateria->id_mesa_examen;
    }

}
