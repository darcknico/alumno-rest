<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MateriaModificado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id_materia;
    public $id_plan_estudio;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($materia)
    {
        $this->id_materia = $materia->id;
        $this->id_plan_estudio = $materia->id_plan_estudio;
    }
}
