<?php

namespace App\Mail;

use App\Models\PaymentMercadoPago;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PreferenciaPagoCreado extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $pago;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(PaymentMercadoPago $pago)
    {
        $this->pago = $pago;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mails.preferencia.creado')
            ->from('no-replay@gmail.com','Sistema de Alumnos')
            ->subject("Pago de cuota en el Sistema de Alumnos")
            ->replyTo("no-replay@gmail.com","No Responder");
    }
}
