<?php

namespace App\Jobs;

use App\Models\Alumno;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AlumnoInscripcionMailEnviado implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $alumno;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Alumno $alumno)
    {
        $this->alumno = $alumno;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
