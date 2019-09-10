<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', 'UsuarioController@login');
Route::post('register', 'UsuarioController@register');
Route::get('email', 'UsuarioController@concidencias');
/*
Route::get('diarias_abrir', 'DiariaController@abrir');
Route::get('diarias_cerrar', 'DiariaController@cerrar');
*/
Route::post('rearmar','PlanPagoController@rearmar_todo');

Route::group(['middleware' => 'auth:api'], function(){
	Route::post('logout', 'UsuarioController@logout');
	Route::get('detalle', 'UsuarioController@details');
	Route::post('token', 'Ajustes\ChatController@token');

	Route::prefix('usuario')->group(function () {
		Route::put('', 'UsuarioController@update');
		Route::post('password', 'UsuarioController@password');
		Route::get('tipos','TipoUsuarioController@index');
		Route::get('sedes/{id_sede}','UsuarioController@sede_seleccionar')->where('id_sede', '[0-9]+');
	});

	Route::apiResource('docentes/materias','Academico\DocenteMateriaController',[
		'as' => 'docenteMateria',
		'parameters' => [
			'materias' => 'docenteMateria',
		],
	]);

	Route::apiResources([
		'docentes' => 'Academico\DocenteController',
		'chat' => 'Ajustes\ChatController',
	]);
	

	Route::prefix('sedes')->group(function () {
		Route::post('','SedeController@store');
		Route::get('','SedeController@index');
		Route::get('buscar','SedeController@buscar');

		Route::get('seleccionar', 'Ajustes\UsuarioSedeController@seleccionado');
		Route::group([
			'prefix'=> '{id_sede}',
			'middleware'=> ['sede'],
			'where'  => ['id_sede' => '[0-9]+'],
		],function () {
			Route::prefix('estadisticas')->group(function(){
				Route::get('pagos', 'HomeController@estadisticas_pagos');
				Route::get('carreras', 'HomeController@estadisticas_carreras');
				Route::get('obligaciones', 'HomeController@estadisticas_obligaciones');
			});
			
			Route::post('seleccionar', 'Ajustes\UsuarioSedeController@seleccionar');
			Route::get('reportes/terminados','Extra\ReporteJobController@terminados');

			Route::apiResources([
				'tramites' => 'TramiteController',
				'alumnos' => 'AlumnoController',
    			'imagenes' => 'PlantillaImagenController',
			]);
			
			Route::apiResource('comisiones/alumnos','Comision\ComisionAlumnoController',[
				'as' => 'comisionAlumno',
				'parameters' => [
					'alumnos' => 'comisionAlumno',
				],
			]);
			Route::apiResource('comisiones/docentes','Comision\ComisionDocenteController',[
				'as' => 'comisionDocente',
				'parameters' => [
					'docentes' => 'comisionDocente',
				],
			]);
			Route::apiResource('comisiones/examenes/alumnos','Comision\ExamenAlumnoController',[
				'as' => 'examenAlumno',
				'parameters' => [
					'alumnos' => 'examenAlumno',
				],
				'except' => [
					'store' ,
					'destroy' ,
				]
			]);
			Route::apiResource('comisiones/asistencias/alumnos','Comision\AsistenciaAlumnoController',[
				'as' => 'asistenciaAlumno',
				'parameters' => [
					'alumnos' => 'asistenciaAlumno',
				],
				'except' => [
					'store' ,
					'destroy' ,
				]
			]);

			Route::apiResource('novedades/sistemas','Novedad\SistemaController',[
				'as' => 'novedadSistema',
				'parameters' => [
					'sistema' => 'novedadSistema',
				],
			]);

			Route::apiResource('reportes','Extra\ReporteJobController',[
				'as' => 'reporteJob',
				'parameters' => [
					'reportes' => 'reporteJob',
				],
				'except' => [
					'store' ,
					'update' ,
				]
			]);

			Route::get('','SedeController@show');
			Route::put('','SedeController@update');
			Route::delete('','SedeController@destroy');

			Route::prefix('inscripciones')->group(function(){
				Route::get('','InscripcionController@index');

				Route::get('estadisticas','InscripcionController@estadisticas');

				Route::group([
					'prefix'=> '{id_inscripcion}',
					'where'  => ['id_inscripcion' => '[0-9]+'],
				],function () {
					Route::get('','InscripcionController@show');
					Route::put('','InscripcionController@update');
					Route::delete('','InscripcionController@destroy');

					Route::put('estado','InscripcionController@estado');

					Route::get('comisiones','ComisionController@inscripcion');

					Route::prefix('mesas')->group(function(){
						Route::get('disponibles','Mesa\MesaExamenMateriaController@inscripcion_disponibles');
						Route::get('materias','Mesa\MesaExamenMateriaController@inscripcion');
						Route::get('{id_mesa_examen}/materias/disponibles','Mesa\MesaExamenMateriaController@inscripcion_materias_disponibles')->where('id_mesa_examen', '[0-9]+');
					});

					Route::prefix('planes_pago')->group(function(){
						Route::get('','InscripcionController@planes_pago');
					});

					Route::prefix('notas')->group(function(){
						Route::get('','Mesa\AlumnoMateriaNotaController@index');
						Route::post('','Mesa\AlumnoMateriaNotaController@store');

						Route::group([
							'prefix'=> '{id_alumno_materia_nota}',
							'where'  => ['id_alumno_materia_nota' => '[0-9]+'],
						],function () {
							Route::put('','Mesa\AlumnoMateriaNotaController@update');
							Route::delete('','Mesa\AlumnoMateriaNotaController@destroy');
						});

						Route::post('importar/previa','Mesa\AlumnoMateriaNotaController@importar_previa');
						Route::post('importar','Mesa\AlumnoMateriaNotaController@importar');
					});
					
					Route::get('reportes/ficha','InscripcionController@reporte_ficha');
					Route::get('reportes/constancia','InscripcionController@reporte_constancia_regular');
					Route::get('reportes/analitico','Mesa\AlumnoMateriaNotaController@reporte');

					Route::prefix('estados')->group(function(){
						Route::get('deuda','InscripcionController@estado_deuda');
					});

					Route::get('asistencias','InscripcionController@asistencias');
					Route::get('examenes','InscripcionController@examenes');
					Route::get('pagos','InscripcionController@pagos');
					
				});
				Route::get('carreras/{id_carrera}','InscripcionController@carreras_alumnos');
				Route::get('exportar','InscripcionController@exportar');

				Route::get('notas/importar/ejemplo','Mesa\AlumnoMateriaNotaController@importar_ejemplo');

			});

			Route::prefix('alumnos')->group(function(){

				Route::group([
					'prefix'=> '{id_alumno}',
					'middleware'=> ['alumno'],
					'where'  => ['id_alumno' => '[0-9]+'],
				],function () {

					Route::get('inscripciones','AlumnoController@inscripciones');
					Route::post('inscripciones','AlumnoController@inscripcion_store');
				});

				Route::post('importar/previa','AlumnoController@importar_previa');
				Route::post('importar','AlumnoController@importar');
				Route::get('exportar','AlumnoController@exportar');
			});

			Route::prefix('planes_pago')->group(function(){
				Route::get('','PlanPagoController@index');
				Route::post('','PlanPagoController@store');
				
				Route::get('estadisticas','PlanPagoController@estadisticas');

				Route::group([
					'prefix'=> '{id_plan_pago}',
					'where'  => ['id_plan_pago' => '[0-9]+'],
				],function () {
					Route::get('','PlanPagoController@show');
					Route::put('','PlanPagoController@update');
					Route::delete('','PlanPagoController@destroy');
					Route::get('pagos','PlanPagoController@pagos');
					Route::get('cuotas','PlanPagoController@cuotas');
					Route::get('matricula','PlanPagoController@matricula');
					
					Route::get('siguiente','PlanPagoController@obligacion_siguiente');

					Route::get('cuenta_corriente','PlanPagoController@cuenta_corriente');
					Route::put('cuenta_corriente','PlanPagoController@rearmar');

					Route::put('pagar','PlanPagoController@pagoPreparar');
					Route::post('pagar','PlanPagoController@pagar');

					Route::put('bonificar','PlanPagoController@bonificarPreparar');
					Route::post('bonificar','PlanPagoController@bonificar');

					Route::post('matricula','PlanPagoController@pagar_matricula');
				});

				Route::get('exportar','PlanPagoController@exportar');
				Route::get('exportar/alumnos','PlanPagoController@exportar_alumnos');


				Route::prefix('precios')->group(function(){
					Route::get('ultimo','PlanPagoPrecioController@ultimo');
					Route::post('','PlanPagoPrecioController@store');
					Route::delete('{id_plan_pago_precio}','PlanPagoPrecioController@store')->where('id_plan_pago_precio','[0-9]+');
				});
			});

			Route::prefix('pagos')->group(function(){
				Route::get('','PagoController@index');
				Route::get('estadisticas','PagoController@estadisticas');
				Route::get('estadisticas/cuenta_corriente','PagoController@estadistica_cuenta_corriente');

				Route::group([
					'prefix'=> '{id_pago}',
					'where'  => ['id_pago' => '[0-9]+'],
				],function () {
					Route::get('','PagoController@show');
					Route::delete('','PagoController@destroy');
					Route::get('reportes','PagoController@reporte_pago');
				});

				Route::get('exportar','PagoController@exportar');
			});

			Route::prefix('plantillas')->group(function () {
				Route::get('','PlantillaController@index');
				Route::post('','PlantillaController@store');
				Route::get('buscar','PlantillaController@buscar');

				Route::group([
					'prefix'=> '{id_plantilla}',
					'where'  => ['id_plantilla' => '[0-9]+'],
					],function () {
						Route::get('','PlantillaController@show');
						Route::put('','PlantillaController@update');
						Route::delete('','PlantillaController@destroy');

						Route::post('archivos','PlantillaController@archivoAlta');
						Route::get('archivos/{id_archivo}','PlantillaController@archivo')->where('id_archivo', '[0-9]+');
						Route::delete('archivos/{id_archivo}','PlantillaController@archivoBaja')->where('id_archivo', '[0-9]+');
				});

				Route::post('enviar','PlantillaController@enviar');
			});

			Route::prefix('notificaciones')->group(function () {
				Route::get('','NotificacionController@index');
				Route::get('enviadas','NotificacionController@enviadas');
				Route::post('','NotificacionController@store');

				Route::group([
					'prefix'=> '{id_notificacion}',
					'where'  => ['id_notificacion' => '[0-9]+'],
					],function () {
						Route::get('','NotificacionController@show');
						Route::get('alumnos','NotificacionController@alumnos');
						Route::put('','NotificacionController@update');
						Route::delete('','NotificacionController@destroy');
						Route::get('desplegar','NotificacionController@desplegar');
						Route::post('fecha','NotificacionController@fecha');
					});
				
				Route::post('alumnos','NotificacionController@filtrar');
			});

			Route::prefix('diarias')->group(function(){
				Route::get('','DiariaController@index');
				Route::post('','DiariaController@store');

				Route::get('ultimos','DiariaController@ultimos');

				Route::group([
					'prefix'=> '{id_diaria}',
					'where'  => ['id_diaria' => '[0-9]+'],
					],function () {
						Route::get('','DiariaController@show');
						Route::delete('','DiariaController@destroy');
						Route::put('','DiariaController@update');
						
						Route::get('siguiente','DiariaController@siguiente');
						Route::get('anterior','DiariaController@anterior');
						Route::get('exportar','DiariaController@exportar');
					});
			});

			Route::prefix('movimientos')->group(function(){
				Route::get('','MovimientoController@index');
				Route::post('ingresos','MovimientoController@ingreso');
				Route::post('egresos','MovimientoController@egreso');
				Route::group([
					'prefix'=> '{id_movimiento}',
					'where'  => ['id_movimiento' => '[0-9]+'],
				],function () {
					Route::get('','MovimientoController@show');
					Route::put('','MovimientoController@update');
					Route::delete('','MovimientoController@destroy');
				});

				Route::get('exportar','MovimientoController@exportar');
				
			});

			Route::prefix('tipos_movimiento')->group(function(){
				Route::get('','TipoMovimientoController@index');
				Route::post('','TipoMovimientoController@store');
				Route::group([
					'prefix'=> '{id_tipo_movimiento}',
					'where'  => ['id_tipo_movimiento' => '[0-9]+'],
				],function () {
					Route::get('','TipoMovimientoController@show');
					Route::put('','TipoMovimientoController@update');
					Route::delete('','TipoMovimientoController@destroy');
				});

				Route::get('ingresos','TipoMovimientoController@ingresos');
				Route::get('egresos','TipoMovimientoController@egresos');
			});


			///////////////////////////// COMISIONES

			Route::prefix('comisiones')->group(function(){
				Route::get('','ComisionController@index');
				Route::post('','ComisionController@store');
				Route::group([
					'prefix'=> '{id_comision}',
					'where'  => ['id_comision' => '[0-9]+'],
				],function () {
					Route::post('asistencias','AsistenciaController@store');
					Route::post('examenes','Comision\ExamenController@store');

					Route::get('','ComisionController@show');
					Route::put('','ComisionController@update');
					Route::delete('','ComisionController@destroy');

					Route::prefix('alumnos')->group(function(){
						Route::get('','ComisionController@alumnos');
						Route::get('disponibles','ComisionController@alumnos_disponibles');

						Route::group([
							'prefix'=> '{id_alumno}',
							'where'  => ['id_alumno' => '[0-9]+'],
						],function () {
							Route::post('','ComisionController@alumno_asociar');
							Route::delete('','ComisionController@alumno_desasociar');
						});
					});

					Route::prefix('docentes')->group(function(){
						Route::get('','ComisionController@docentes');
					});
					
					Route::get('asistencias','ComisionController@asistencias');
					Route::get('examenes','ComisionController@examenes');
					Route::get('reporte','ComisionController@reporte');
				});

				Route::get('carreras/{id_carrera}','ComisionController@index');
				Route::get('materias/{id_materia}','ComisionController@index');
			});

			Route::prefix('asistencias')->group(function(){
				Route::get('','AsistenciaController@index');

				Route::group([
					'prefix'=> '{id_asistencia}',
					'where'  => ['id_asistencia' => '[0-9]+'],
				],function () {
					Route::get('','AsistenciaController@show');
					Route::delete('','AsistenciaController@destroy');

					Route::get('check_in','AsistenciaController@check_in');
					Route::post('check_out','AsistenciaController@check_out');
					Route::post('check_out/previa','AsistenciaController@check_out_previa');

					Route::post('alumnos/{id_alumno}','AsistenciaController@alumno');
					Route::get('alumnos','AsistenciaController@alumnos');

				});
			});

			Route::prefix('examenes')->group(function(){
				Route::get('','Comision\ExamenController@index');

				Route::group([
					'prefix'=> '{id_comision_examen}',
					'where'  => ['id_comision_examen' => '[0-9]+'],
				],function () {
					Route::get('','Comision\ExamenController@show');
					Route::put('','Comision\ExamenController@update');
					Route::delete('','Comision\ExamenController@destroy');

					Route::post('alumnos/{id_alumno}','Comision\ExamenController@alumno');
					Route::get('alumnos','Comision\ExamenController@alumnos');

				});
			});

			/////////////////////////////////// MESAS DE EXAMENES
			Route::prefix('mesas')->group(function(){
				Route::get('','Mesa\MesaExamenController@index');
				Route::post('','Mesa\MesaExamenController@store');

				Route::group([
					'prefix'=> '{id_mesa_examen}',
					'where'  => ['id_mesa_examen' => '[0-9]+'],
				],function () {
					Route::get('','Mesa\MesaExamenController@show');
					Route::put('','Mesa\MesaExamenController@update');
					Route::delete('','Mesa\MesaExamenController@destroy');

					Route::prefix('materias')->group(function(){
						Route::get('','Mesa\MesaExamenController@materias');
						Route::get('disponibles','Mesa\MesaExamenController@materias_disponibles');
						Route::get('disponibles/comision','Mesa\MesaExamenController@materias_disponibles_comision');

						Route::group([
							'prefix'=> '{id_materia}',
							'where'  => ['id_alumno' => '[0-9]+'],
						],function () {
							Route::post('','Mesa\MesaExamenController@materia_asociar');
							Route::delete('','Mesa\MesaExamenController@materia_desasociar');
						});
						
					});
					
					Route::get('reportes/resumen','Mesa\MesaExamenController@reporte_resumen');

				});

				//////////////////////////  MESAS DE EXAMENES PARA LA MATERIA
				Route::prefix('materias')->group(function(){
					Route::get('','Mesa\MesaExamenMateriaController@index');
					Route::post('','Mesa\MesaExamenMateriaController@store');

					Route::group([
						'prefix'=> '{id_mesa_examen_materia}',
						'where'  => ['id_mesa_examen_materia' => '[0-9]+'],
					],function () {
						Route::get('','Mesa\MesaExamenMateriaController@show');
						Route::put('','Mesa\MesaExamenMateriaController@update');
						Route::delete('','Mesa\MesaExamenMateriaController@destroy');

						Route::get('check_in','Mesa\MesaExamenMateriaController@check_in');
						Route::post('check_out','Mesa\MesaExamenMateriaController@check_out');
						Route::post('check_out/previa','Mesa\MesaExamenMateriaController@check_out_previa');

						Route::prefix('alumnos')->group(function(){
							Route::get('','Mesa\MesaExamenMateriaController@alumnos');

							Route::group([
								'prefix'=> '{id_alumno}',
								'where'  => ['id_alumno' => '[0-9]+'],
							],function () {
								Route::post('','Mesa\MesaExamenMateriaController@alumno_asociar');
								Route::delete('','Mesa\MesaExamenMateriaController@alumno_desasociar');
							});

							Route::group([
								'prefix'=> '{id_mesa_examen_materia_alumno}',
								'where'  => ['id_mesa_examen_materia_alumno' => '[0-9]+'],
							],function () {
								Route::get('','Mesa\MesaExamenMateriaAlumnoController@show');
								Route::put('','Mesa\MesaExamenMateriaAlumnoController@update');
							});
						});

						Route::prefix('docentes')->group(function(){
							Route::get('','Mesa\MesaExamenMateriaController@docentes');
							
						});
						
						Route::post('cerrar','Mesa\MesaExamenMateriaController@cerrar');
						Route::get('reportes/acta','Mesa\MesaExamenMateriaController@reporte_acta');
					});
					
					Route::post('reportes/acta','Mesa\MesaExamenMateriaController@reporte_acta_masivo');

					Route::group(['prefix' => 'alumnos'], function() {
					    Route::get('','Mesa\MesaExamenMateriaAlumnoController@index');
					    Route::post('','Mesa\MesaExamenMateriaAlumnoController@store');
						Route::group([
							'prefix'=> '{id_mesa_examen_materia_alumno}',
							'where'  => ['id_mesa_examen_materia_alumno' => '[0-9]+'],
						],function () {
							Route::get('','Mesa\MesaExamenMateriaAlumnoController@show');
							Route::put('','Mesa\MesaExamenMateriaAlumnoController@update');
							Route::delete('','Mesa\MesaExamenMateriaAlumnoController@update');

							Route::get('reportes/constancia','Mesa\MesaExamenMateriaAlumnoController@reporte_constancia');
						});
					});

					Route::group(['prefix' => 'docentes'], function() {
					    Route::get('','Mesa\MesaExamenMateriaDocenteController@index');
					    Route::post('','Mesa\MesaExamenMateriaDocenteController@store');
						Route::group([
							'prefix'=> '{id_mesa_examen_materia_docente}',
							'where'  => ['id_mesa_examen_materia_docente' => '[0-9]+'],
						],function () {
							Route::get('','Mesa\MesaExamenMateriaDocenteController@show');
							Route::put('','Mesa\MesaExamenMateriaDocenteController@update');
							Route::delete('','Mesa\MesaExamenMateriaDocenteController@destroy');
						});
					});
				});
			});

			/////////////////////////////// NOVEDADES

			Route::group(['prefix' => 'novedades'], function() {
			    Route::group(['prefix' => 'sistemas'], function() {
			        Route::group([
			        	'prefix' => '{id_novedad_sistema}',
						'where'  => ['id_novedad_sistema' => '[0-9]+'],
			        ], function() {
			        	Route::post('mostrar','Novedad\SistemaController@mostrar');
			        	Route::get('usuarios','Novedad\SistemaController@usuarios');
			        });
			    });
			});
		
			Route::prefix('auditorias')->group(function(){
				Route::get('alumnos', 'Extra\AuditoriaController@alumnos');
			});
		});
	});

	Route::prefix('departamentos')->group(function(){
		Route::post('','DepartamentoController@store');
		Route::get('','DepartamentoController@index');

		Route::group([
			'prefix'=> '{id_departamento}',
			'where'  => ['id_departamento' => '[0-9]+'],
		],function () {
			Route::get('','DepartamentoController@show');
			Route::put('','DepartamentoController@update');
			Route::delete('','DepartamentoController@destroy');

			Route::prefix('carreras')->group(function(){
				Route::post('','CarreraController@store');
				Route::get('','CarreraController@index');

			});
		});

	});

	Route::prefix('carreras')->group(function(){
		Route::post('','CarreraController@store');
		Route::get('','CarreraController@index');

		Route::get('estadisticas','CarreraController@estadisticas');

		Route::group([
			'prefix'=> '{id_carrera}',
			'where'  => ['id_carrera' => '[0-9]+'],
		],function () {
			Route::get('','CarreraController@show');
			Route::put('','CarreraController@update');
			Route::delete('','CarreraController@destroy');

			Route::post('modalidades/{id_modalidad}','CarreraController@modalidad_asociar')->where('id_modalidad','[0-9]+');
			Route::delete('modalidades/{id_modalidad}','CarreraController@modalidad_desasociar')->where('id_modalidad','[0-9]+');

			Route::prefix('planes_estudio')->group(function(){
				Route::post('','PlanEstudioController@store');
				Route::get('','PlanEstudioController@index');

				Route::group([
					'prefix'=> '{id_plan_estudio}',
					'where'  => ['id_plan_estudio' => '[0-9]+'],
				],function () {
					Route::get('','PlanEstudioController@show');
					Route::put('','PlanEstudioController@update');
					Route::delete('','PlanEstudioController@destroy');

					Route::post('/seleccionar','CarreraController@seleccionar_plan');

				});
			});
		});
	});

	Route::prefix('planes_estudio')->group(function(){
		Route::get('','PlanEstudioController@index');
		Route::group([
			'prefix'=> '{id_plan_estudio}',
			'where'  => ['id_plan_estudio' => '[0-9]+'],
		],function () {
			Route::get('','PlanEstudioController@show');
			Route::put('','PlanEstudioController@update');
			Route::delete('','PlanEstudioController@destroy');
			Route::post('seleccionar','CarreraController@seleccionar_plan');
			Route::get('reportes','PlanEstudioController@reporte');
		});
	});

	Route::prefix('materias')->group(function(){
		Route::post('','MateriaController@store');
		Route::get('','MateriaController@index');

		Route::group([
			'prefix'=> '{id_materia}',
			'where'  => ['id_materia' => '[0-9]+'],
		],function () {
			Route::get('','MateriaController@show');
			Route::put('','MateriaController@update');
			Route::delete('','MateriaController@destroy');

			Route::post('correlativas/{correlatividad_id_materia}','MateriaController@correlatividad_asociar')->where('correlatividad_id_materia','[0-9]+');
			Route::delete('correlativas/{correlatividad_id_materia}','MateriaController@correlatividad_desasociar')->where('correlatividad_id_materia','[0-9]+');
		});
	});

	Route::prefix('modalidades')->group(function(){
		Route::post('','ModalidadController@store');
		Route::get('','ModalidadController@index');

		Route::group([
			'prefix'=> '{id_modalidad}',
			'where'  => ['id_modalidad' => '[0-9]+'],
		],function () {
			Route::get('','ModalidadController@show');
			Route::put('','ModalidadController@update');
			Route::delete('','ModalidadController@destroy');
		});
	});

	Route::prefix('becas')->group(function(){
		Route::post('','BecaController@store');
		Route::get('','BecaController@index');

		Route::group([
			'prefix'=> '{id_beca}',
			'where'  => ['id_beca' => '[0-9]+'],
		],function () {
			Route::get('','BecaController@show');
			Route::put('','BecaController@update');
			Route::delete('','BecaController@destroy');
		});
	});

	Route::prefix('usuarios')->group(function () {
		Route::get('','UsuarioController@index');
		Route::post('','UsuarioController@store');

		Route::group([
			'prefix'=> '{id_usuario}',
			'where'  => ['id_usuario' => '[0-9]+'],
		],function () {

			Route::apiResources([
				'archivos' => 'Ajustes\UsuarioArchivoController',
			]);

			Route::get('','UsuarioController@show');
			Route::put('','UsuarioController@edit');
			Route::put('password','UsuarioController@changePassword');
			Route::delete('','UsuarioController@destroy');
			Route::get('desbloquear','UsuarioController@desbloquear');

			Route::post('sedes/{id_sede}','UsuarioController@sede_asociar')->where('id_sede', '[0-9]+');
			Route::delete('sedes/{id_sede}','UsuarioController@sede_desasociar')->where('id_sede', '[0-9]+');
		});

	});

	Route::prefix('alumnos')->group(function () {
		Route::get('coincidencia','AlumnoController@coincidencia');

		Route::get('estado/tipos','AlumnoController@tipos_estado');
		Route::get('civil/tipos','AlumnoController@tipos_civil');
		Route::get('documentacion/tipos','AlumnoController@tipos_documentacion');
		Route::get('condicion/tipos','Mesa\MesaExamenMateriaController@condiciones');

		Route::get('','AlumnoController@index');

		Route::get('estadisticas','AlumnoController@estadisticas');

		Route::apiResource('sedes','Academico\AlumnoSedeController',[
			'as' => 'alumnoSede',
			'parameters' => [
				'sedes' => 'alumnoSede',
			],
			'except' => [
				'update',
			]
		]);

		Route::group([
			'prefix'=> '{id_alumno}',
			'middleware'=> ['alumno'],
			'where'  => ['id_alumno' => '[0-9]+'],
		],function () {
			Route::get('','AlumnoController@show');
			Route::get('sedes','AlumnoController@sedes');
			Route::post('password','AlumnoController@password');

			Route::post('archivos','AlumnoController@archivoAlta');
			Route::get('archivos/{id_alumno_archivo}','AlumnoController@archivo')->where('id_alumno_archivo','[0-9]+');
			Route::delete('archivos/{id_alumno_archivo}','AlumnoController@archivoBaja')->where('id_alumno_archivo','[0-9]+');

			Route::get('inscripciones','AlumnoController@inscripciones');
			Route::post('inscripciones','AlumnoController@inscripcion_store');

			Route::prefix('estados')->group(function(){
				Route::get('deuda','AlumnoController@estado_deuda');
			});

		});
	});

	Route::prefix('planes_pago')->group(function(){
		Route::post('previa','PlanPagoController@previa');
	});

	Route::prefix('tipos')->group(function () {
		Route::get('contratos','TipoController@contratos');
		Route::get('docentes/mesas','TipoController@mesa_docente');
	});


	Route::prefix('diarias')->group(function () {
		Route::get('rearmar','DiariaController@rearmar');
	});

	Route::prefix('inscripciones')->group(function () {
		Route::get('estado/tipos','InscripcionController@tipos_estado');
	});

	Route::prefix('materias')->group(function () {
		Route::get('regimen/tipos','MateriaController@tipos_regimen');
		Route::get('lectivo/tipos','MateriaController@tipos_lectivo');
	});

	Route::prefix('asistencias')->group(function () {
		Route::get('alumnos/tipos','AsistenciaController@tipos');
	});

	Route::prefix('examenes')->group(function () {
		Route::get('tipos','Comision\ExamenController@tipos');
	});

	Route::prefix('documentos')->group(function () {
		Route::get('tipos','TipoDocumentoController@index');
	});

	Route::prefix('pagos')->group(function () {
		Route::get('tipos','PagoController@tipos');
	});

	Route::prefix('movimientos')->group(function () {
		Route::get('formas','MovimientoController@formas');
		Route::get('comprobantes','MovimientoController@tipos_comprobante');
	});

	Route::prefix('extras')->group(function () {
		Route::get('provincias','Extra\\ProvinciaController@provincias');
		Route::get('localidades','Extra\\ProvinciaController@localidades');
	});

	Route::prefix('tramites')->group(function () {
		Route::get('tipos','TramiteController@tipos');
	});
});
