<?php

namespace App\Providers;

use App\Models\Sede;
use App\Models\Departamento;
use App\Models\Carrera;
use App\Models\PlanEstudio;
use App\Models\Materia;
use App\Models\Alumno;
use App\Models\Pago;
use App\Models\Venta;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
        Route::bind('sede', function ($value) {
            return Sede::where([
                'sed_id' => $value,
                'estado' => 1,
            ])->first() ?? abort(404);
        });
        Route::bind('departamento', function ($value) {
            return Departamento::where([
                'dep_id' => $value,
                'estado' => 1,
            ])->first() ?? abort(404);
        });
        Route::bind('carrera', function ($value) {
            return Carrera::where([
                'car_id' => $value,
                'estado' => 1,
            ])->first() ?? abort(404);
        });
        Route::bind('plan_estudio', function ($value) {
            $id_sede = request()->route('id_sede',0);
            $carrera = request()->route('carrera',0);

            return PlanEstudio::where([
                'pes_id' => $value,
                'estado' => 1,
                'car_id' => $carrera,
            ])->first() ?? abort(404);
        });
        Route::bind('materia', function ($value) {
            return Materia::where([
                'mat_id' => $value,
                'estado' => 1,
            ])->first() ?? abort(404);
        });
        /*
        Route::bind('alumno', function ($value) {
            $todo = Alumno::where([
                'alu_id' => $value,
                'estado' => 1,
            ]);
            $id_sede = request()->route('id_sede',0);
            if ($id_sede>0) {
                $todo = $todo->where('sed_id',$id_sede);
            }
            return $todo->firstOrFail();
        });
        */
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }
}
