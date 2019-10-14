<?php

namespace App\Http\Controllers;

use App\Models\Obligacion;
use Illuminate\Http\Request;

class ObligacionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return response()->json([]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return response()->json([]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Obligacion  $obligacion
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $obligacion = Obligacion::with('pagos.pago','intereses','interes','obligacion','tipo')->find($request->obligacion);
        return response()->json($obligacion);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Obligacion  $obligacion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $descripcion = $request->input('descripcion');
        $obligacion = Obligacion::find($request->obligacion);
        //$obligacion->descripcion = $descripcion;
        return response()->json($obligacion);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Obligacion  $obligacion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        return response()->json([]);
    }
}
