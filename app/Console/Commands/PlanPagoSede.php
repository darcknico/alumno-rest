<?php

namespace App\Console\Commands;

use App\Models\Sede;
use App\Models\PlanPago;
use App\Models\Inscripcion;
use App\Models\Carrera;
use App\Models\Pago;
use App\Models\Movimiento;
use App\Models\Obligacion;
use App\Models\ObligacionPago;
use App\Models\ObligacionInteres;

use App\Functions\CuentaCorrienteFunction;
use App\Functions\DiariaFunction;
use App\Functions\PlanPagoFunction;
use App\Functions\ObligacionFunction;
use Carbon\Carbon;

use Illuminate\Console\Command;

class PlanPagoSede extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plan_pago:sede {id_sede=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dado el sede, busca los planes de pago del año pasado, y genera el nuevo con los nuevos montos';

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
        $id_sede = $this->argument('id_sede');
        $sede = Sede::find($id_sede);
        if($sede){
            $this->info('Sede: '.$sede->nombre);
            $fecha = Carbon::now();
            $planes = PlanPago::where('anio',$fecha->year - 1)
            ->where('estado',1)
            ->where('id_sede',$id_sede)
            ->get();
            $fecha_nuevo = Carbon::parse("2020-02-01");
            $precios = CuentaCorrienteFunction::ultimo_precio_plan($id_sede);
            $dias_vencimiento = 9;
            $cuota_cantidad = 10;
            $matricula_monto = $precios->matricula_monto;
            $cuota_monto = $precios->cuota_monto;
            $interes_monto = $precios->interes_monto;
            $anio = 2020;
            $this->info('Cantidad de planes: '.count($planes));
            foreach ($planes as $plan) {
                $plan_pago = PlanPago::where('id_inscripcion',$plan->id_inscripcion)
                ->where('estado',1)
                ->where('id_sede',$id_sede)
                ->where('anio',$anio)
                ->first();
                if($plan_pago){

                } else {
                    $inscripcion = Inscripcion::find($plan->id_inscripcion);
                    $beca_porcentaje = 0;
                    if($inscripcion){
                        $beca_porcentaje = $inscripcion->beca_porcentaje;
                    }
                    $plan_pago = new PlanPago;
                    $plan_pago->id_sede = $id_sede;
                    $plan_pago->id_inscripcion = $plan->id_inscripcion;
                    $plan_pago->matricula_monto = $matricula_monto;
                    $plan_pago->matricula_saldo = $matricula_monto;
                    $plan_pago->cuota_monto = $cuota_monto;
                    $plan_pago->interes_monto = $interes_monto;
                    $plan_pago->anio = $anio;
                    $plan_pago->cuota_cantidad = $cuota_cantidad;
                    $plan_pago->fecha = $fecha_nuevo;
                    $plan_pago->dias_vencimiento = $dias_vencimiento;
                    $plan_pago->id_usuario = 1;
                    $plan_pago->save();
                    $detalle = PlanPagoFunction::preparar_obligaciones(
                        $anio,
                        $matricula_monto,
                        $cuota_monto,
                        $beca_porcentaje,
                        $cuota_cantidad,
                        $dias_vencimiento,
                        $fecha_nuevo);
                    foreach ($detalle['obligaciones'] as $obligacion) {
                      $cuota = new Obligacion;
                      $cuota->id_plan_pago = $plan_pago->id;
                      $cuota->id_tipo_obligacion = $obligacion['id_tipo_obligacion'];
                      $cuota->descripcion = $obligacion['descripcion'];
                      $cuota->monto = $obligacion['monto'];
                      $cuota->saldo = $obligacion['saldo'];
                      $cuota->fecha = $obligacion['fecha'];
                      $cuota->id_usuario = 1;
                      $cuota->fecha_vencimiento = $obligacion['fecha_vencimiento'];
                      $cuota->save();
                    }
                    CuentaCorrienteFunction::armar($id_sede,$plan_pago->id);
                }
            }
        }
    }
}
