<?php

namespace App\Console\Commands;

use App\Models\Extra\Feriado;

use Carbon\Carbon;

use Illuminate\Console\Command;

class FeriadoActualizar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feriado:actualizar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consulta a la API de feriados y extraer los ultimos cambios';

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
        $client = new \GuzzleHttp\Client();
        $anio = Carbon::now()->year;
        $request = $client->get('http://nolaborables.com.ar/api/v2/feriados/'.$anio);
        $response = $request->getBody();
        $json = json_decode($response,true);


    }

    /**
    [
        {
            "motivo": "Año Nuevo",
            "tipo": "inamovible",
            "dia": 1,
            "mes": 1,
            "id": "año-nuevo"
        },
    ]
    */
}
