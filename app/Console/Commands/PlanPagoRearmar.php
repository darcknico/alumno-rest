<?php

namespace App\Console\Commands;

use App\Models\Sede;
use App\Models\PlanPago;

use App\Functions\CuentaCorrienteFunction;

use Illuminate\Console\Command;

class PlanPagoRearmar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plan_pago:rearmar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A todas las sedes habilitadas y para todos los planes de pago habilitados, realiza la funcion de rearmar';

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
        $sedes = Sede::where('estado',1)->get();
        $salida = [];
        foreach ($sedes as $sede) {
          $planes = PlanPago::where([
            'estado' => 1,
            'sed_id' => $sede->id,
          ])->get();
          foreach ($planes as $plan) {
            $todo = CuentaCorrienteFunction::armar($sede->id,$plan->id);
            $salida[]=$plan;
          }
        }
    }
}
