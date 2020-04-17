<?php

namespace App\Mail;

use App\Models\Mesa\MesaExamenMateria;
use App\Models\Mesa\MesaExamenMateriaAlumno;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MesaExamenMateriaAlumnoInscripcionNuevo extends Mailable
{
    use Queueable, SerializesModels;

    public $mesa_examen_materia_alumno;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(MesaExamenMateriaAlumno $item)
    {
        $this->mesa_examen_materia_alumno = $item;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mesa_examen_materia = $this->mesa_examen_materia_alumno->mesa_examen_materia;
        $alumno = $this->mesa_examen_materia_alumno->alumno;
        return $this->markdown('mails.mesa_examen.alumno_inscripcion_nuevo')
            ->with([
                'mesa_examen_materia_alumno' => $this->mesa_examen_materia_alumno,
                'mesa_examen_materia' => $mesa_examen_materia,
                'alumno' => $alumno,
            ])
            ->subject('Inscripcion a mesa de examen en '.$mesa_examen_materia->materia->nombre)
            ->replyTo("no-replay@prueba.com","No Responder");
    }
}
