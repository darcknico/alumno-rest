<?php

namespace App\Functions;

use Carbon\Carbon;

class AuxiliarFunction{

	public static function if_in_array(array $array, $object,$array_key="id",$object_key="id") 
	{
		foreach ($array as $item) {
			if(is_null($object_key)){
				if ($item[$array_key] == $object) {
					return true;
				}
			} else {
				if ($item[$array_key] == $object[$object_key]) {
					return true;
				}
			}
			
		}
		return false;
	}

	public static function rename($json){
        $json = json_decode($json);
        $resultado = [];
        foreach ($json as $key => $value) {
            $new_key = substr($key, 4);
            $resultado[$new_key] = $value;
        }
        return $resultado;
    }
}