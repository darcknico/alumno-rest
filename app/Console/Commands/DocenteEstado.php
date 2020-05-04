<?php

namespace App\Console\Commands;

use App\Models\Academico\Docente;
use App\Functions\DocenteFunction;
use Illuminate\Console\Command;

class DocenteEstado extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docente:estado';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza el estado del docente';

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
        $this->info('COMENZANDO DOCENTE ESTADO');
        $cantidad = Docente::where('id_tipo_docente_estado',2)->count();
        $this->info('Total de docentes a checkear: '.$cantidad);
        $docentes = Docente::where('id_tipo_docente_estado',2)
            ->get();
        foreach ($docentes as $docente) {
            DocenteFunction::actualizar($docente);
        }
        $this->info('TERMINANDO DOCENTE ESTADO');
    }
}
