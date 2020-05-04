<?php

namespace App\Events;

use App\Models\Academico\Docente;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DocenteModificado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $docente;
    public $id_usuario;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Docente $docente)
    {
        $this->docente = $docente;
        $this->id_usuario = $docente->id;
    }

}
