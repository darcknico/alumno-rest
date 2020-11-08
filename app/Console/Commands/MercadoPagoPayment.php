<?php

namespace App\Console\Commands;

use App\Models\PaymentMercadoPago;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreferenciaPagoAprobado;

class MercadoPagoPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mercadopago:payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Controla el estado de los pagos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('COMENZANDO MERCADOPAGO PAYMENT');
        $method = new \App\PaymentMethods\MercadoPago;
        $cantidad = PaymentMercadoPago::whereNotNull('payment_id')
            ->where('payment_status','pending')
            ->count();
        $registros = PaymentMercadoPago::whereNotNull('payment_id')
            ->where('payment_status','pending')
            ->get();

        $this->info('CANTIDAD PENDIENTES '.$cantidad);
        foreach ($registros as $registro) {
            $pago = $method->checkPaymentStatus($registro->payment_id);
            if($pago){
                $this->info('ENVIADO A '.$pago->email);
            }
        }

        $this->info('TERMINANDO MERCADOPAGO PAYMENT');
    }
}
