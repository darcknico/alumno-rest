<?php

namespace App\Mails;

use App\Models\Mesa\MesaExamen;
use App\Models\Mesa\MesaExamenMateria;
use App\Models\Mesa\MesaExamenMateriaDocente;
use App\Models\Academico\Docente;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


use App\Functions\CorreoFunction;

class MesaExamenNotificacion extends Mailable
{
    use Queueable, SerializesModels;

    public $logo;
    public $docente;
    public $mesa_examen;
    public $mesa_examen_materia;
    public $sede;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Docente $docente, MesaExamen $mesa_examen, MesaExamenMateria $mesa_examen_materia)
    {
        $this->logo = CorreoFunction::logo();
        $this->docente = $docente;
        $this->mesa_examen = $mesa_examen;
        $this->mesa_examen_materia = $mesa_examen_materia;
        $this->sede = $mesa_examen->sede;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->markdown('mails.mesa_examen_notificacion')
            ->subject("Notificacion de Mesa de Examen: ".$this->mesa_examen_materia->materia->nombre)
            ->replyTo("no-replay@prueba.com","No Responder");
    }
}