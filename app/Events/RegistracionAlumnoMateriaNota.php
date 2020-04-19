<?php

namespace App\Events;

use App\Models\Mesa\AlumnoMateriaNota;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RegistracionAlumnoMateriaNota
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id_inscripcion;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(AlumnoMateriaNota $item)
    {
        $this->id_inscripcion = $item->id_inscripcion;
    }

}
