<?php
namespace App\Mails;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Functions\CorreoFunction;

class UsuarioPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $logo;
    public $usuario;
    public $password;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $usuario,$password = null)
    {
        $this->logo = CorreoFunction::logo();
        $this->usuario = $usuario;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->markdown('mails.usuario_password')
            ->subject("Cambio de contraseña Usuario: ".$this->usuario->email)
            ->replyTo("no-replay@prueba.com","No Responder");
    }
}