<?php

namespace App\Console\Commands;

use App\Models\Sede;
use App\Models\PlanPago;
use App\Models\Inscripcion;
use App\Models\Obligacion;
use Carbon\Carbon;

use App\Functions\CuentaCorrienteFunction;
use Illuminate\Console\Command;

class PlanPagoInteres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plan_pago:interes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un Interes al mes de vencimiento';

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
        $fecha_corriente = Carbon::now();
        $sedes = Sede::where('estado',1)->get();
        foreach ($sedes as $sede) {
            $planes = PlanPago::where('estado',1)
            ->where('id_sede',$sede->id)
            ->whereHas('obligaciones',function($q)use($fecha_corriente){
                return $q->where('estado',1)
                ->whereDate('fecha_vencimiento','<',$fecha_corriente)
                ->where('saldo','>',0);
            })
            ->whereHas('inscripcion',function($q){
                return $q->where('estado',1)
                    ->whereHas('alumno',function($qt){
                        return $qt->where('estado',1);
                    });
            })
            ->get();
            foreach ($planes as $plan) {
                $todo = CuentaCorrienteFunction::armar($sede->id,$plan->id);
            }
        }
        return $sedes;
    }
}
