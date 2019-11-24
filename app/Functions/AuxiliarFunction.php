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

    public static function randString($length) {
	    $char = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	    $char = str_shuffle($char);
	    for($i = 0, $rand = '', $l = strlen($char) - 1; $i < $length; $i ++) {
	        $rand .= $char{mt_rand(0, $l)};
	    }
	    return $rand;
	}

	public static function is_true($val, $return_null=false){
	    $boolval = ( is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : (bool) $val );
	    return ( $boolval===null && !$return_null ? false : $boolval );
	}

	public static function sanitize_file_name( $string ) {
	    $string = strip_tags($string);
        // Preserve escaped octets.
        $string = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $string);
        // Remove percent signs that are not part of an octet.
        $string = str_replace('%', '', $string);
        // Restore octets.
        $string = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $string);
        if (function_exists('mb_strtolower')) {
            $string = mb_strtolower($string, 'UTF-8');
        } else {
            $string = strtolower($string);
        }
        $string = preg_replace('/\p{Mn}/u', '', \Normalizer::normalize($string, \Normalizer::FORM_KD));
        $string = preg_replace('/[^%a-z0-9 _-]/', '', $string);
        $string = preg_replace('/\s+/', '-', $string);
        $string = preg_replace('|-+|', '-', $string);
        $string = trim($string, '-');
        return $string;
	}
}