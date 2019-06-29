<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS
    |--------------------------------------------------------------------------
    |
    | allowedOrigins, allowedHeaders and allowedMethods can be set to array('*')
    | to accept any value.
    |
    */
   
    'supportsCredentials' => false,
    'allowedOrigins' => ['*'],
    'allowedOriginsPatterns' => [],
    'allowedHeaders' => [
    	'Access-Control-Allow-Origin',
    	'Authorization',
    	'Content-Type',
    	'Accept',
    	'Content-Disposition',
    	'X-Requested-With'],
    'allowedMethods' => ['GET','PUT','POST','DELETE','OPTIONS'],
    'exposedHeaders' => [],
    'maxAge' => 0,

];
