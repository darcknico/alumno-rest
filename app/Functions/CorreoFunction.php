<?php

namespace App\Functions;

use Carbon\Carbon;

class CorreoFunction{

	public static function logo() 
	{
		//$logo = "http://ariasdesaavedra.edu.ar/sistema/alumno-rest/public/img/logosaavedra.jpg";	
		//$logo = "http://alumno-rest.proyectosinformaticos.com.ar/img/logosaavedra.jpg";
		$logo = "http://api.sistema.ariasdesaavedra.edu.ar//img/logosaavedra.jpg";
		//$logo = "http://34.226.235.220/alumno-rest/public/index.php/img/logosaavedra.jpg"
		return $logo;
	}
}