<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ComisionExamenModificado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id_comision;
    public $id_comision_examen;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($examen)
    {
        $this->id_comision_examen = $examen->id;
        $this->id_comision = $examen->id_comision;
    }

}
