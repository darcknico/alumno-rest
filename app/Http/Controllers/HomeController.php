<?php

namespace App\Http\Controllers;

use App\Models\Cuentas\Obligacion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Carbon\Carbon;
use \DB;
class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('welcome');
    }

    public function estadisticas_pagos(Request $request){
        $id_sede = $request->route('id_sede');
        $length = $request->query('length',6);

        $sequence = 'seq_0_to_'.$length;
        $sql = "
            SELECT d.date as fecha, COALESCE(sum(pag.pag_monto), 0) as total
            FROM ( SELECT DATE_FORMAT(CURRENT_DATE,'%Y-%m-%d') - INTERVAL seq DAY AS date 
                FROM ".$sequence." AS offs
                ) d LEFT OUTER JOIN
                tbl_pagos pag 
                ON d.date = pag.pag_fecha and pag.sed_id = ? and pag.estado = 1
            GROUP BY d.date
            order by fecha
                ";
        $results = DB::select($sql, [
            $id_sede,
            ]
            );
        return response()->json($results,200);
    }

    public function estadisticas_carreras(Request $request){
        $id_sede = $request->route('id_sede');
        $length = $request->query('length',5);
        $anio = $request->query('anio',null);
        if(is_null($anio)){
            $anio = Carbon::now()->year;
        }

        $results = DB::select("
                SELECT count(ins.ins_id) as total , car.car_id as id ,car.car_nombre as nombre
                FROM tbl_inscripciones ins
                RIGHT JOIN tbl_carreras car ON car.car_id = ins.car_id
                WHERE ins.estado = true
                AND ins.sed_id = ?
                AND car.estado = true
                AND ins.ins_anio = ?
                GROUP BY car.car_id,car.car_nombre
                LIMIT ?;
                ", [
            $id_sede,
            $anio,
            $length,
            ]
            );
        return response()->json($results,200);
    }

    public function estadisticas_obligaciones(Request $request){
        $id_sede = $request->route('id_sede');
        $anio = $request->query('anio',null);
        if(is_null($anio)){
            $anio = Carbon::now()->year;
        }
        $current_date = $anio.'-1-1';
        $sql = "
            SELECT YEAR(d.date) as anio,
                MONTH(d.date) as mes,
                COALESCE(sum(obl.obl_monto),0) as total
                FROM (SELECT '".$current_date."' + INTERVAL seq month AS date 
                    FROM seq_0_to_11 AS offs
                ) d LEFT OUTER JOIN
                tbl_obligaciones obl 
                ON MONTH(d.date )= MONTH(obl.obl_fecha) AND YEAR(d.date) = YEAR(obl.obl_fecha)
                right join tbl_planes_pago ppa on ppa.ppa_id = obl.ppa_id
            WHERE 
            ppa.estado = 1 and
            ppa.sed_id = ? and
            obl.tob_id = 1 and
            obl.estado = 1
            group by 1,2
            order by 1,2
                ";
        $results = DB::select($sql, [
                $id_sede,
                ]
            );
        return response()->json($results,200);
    }

}
