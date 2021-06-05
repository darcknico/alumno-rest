<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ComisionModificado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $comision;
    public $id_comision;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($comision)
    {
        $this->comision = $comision;
        $this->id_comision = $comision->id;
    }
}
