<?php

namespace App\Mails;

use App\Models\Sede;
use App\Models\Academico\Docente;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificacionDocente extends Mailable
{
    use Queueable, SerializesModels;

    public $docente;
    public $sede;
    public $periodo;
    public $pathToFile;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Docente $docente, Sede $sede, $periodo, $pathToFile)
    {
        $this->docente = $docente;
        $this->sede = $sede;
        $this->periodo = $periodo;
        $this->pathToFile = $pathToFile;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->markdown('mails.notificacion_docente')
            ->subject("Notificacion mesas de examenes periodo ".$this->periodo)
            ->replyTo("no-replay@prueba.com","No Responder")
            ->attach($this->pathToFile, [
                    'as' => $this->sede->nombre.' '.$this->periodo.'.pdf',
                    'mime' => 'application/pdf',
                ]);
    }
}