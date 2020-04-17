<?php

namespace App\Mail;

use App\Models\Comision;
use App\Models\ComisionAlumno;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ComisionAlumnoInscripcionNuevo extends Mailable
{
    use Queueable, SerializesModels;

    public $comisionAlumno;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ComisionAlumno $item)
    {
        $this->comisionAlumno = $item;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $alumno = $this->comisionAlumno->alumno;
        $comision = $this->comisionAlumno->comision;

        return $this->markdown('mails.comision.alumno_inscripcion_nuevo')
            ->with([
                'comision' => $comision,
                'alumno' => $alumno,
                'comisionAlumno' => $this->comisionAlumno,
            ])
            ->subject('Inscripcion a comision de '.$comision->materia->nombre)
            ->replyTo("no-replay@prueba.com","No Responder");
    }
}
