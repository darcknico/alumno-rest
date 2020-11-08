<?php

return [

    'enabled' => [
        'mercadopago',
    ],

    'use_sandbox' => env('SANDBOX_GATEWAYS', true),

    'mercadopago' => [
        'logo' => '/img/payment/mercadopago.png',
        'display' => 'MercadoPago',
        'client' => env('MP_CLIENT_ID'),
        'secret' => env('MP_CLIENT_SECRET'),
        'access_token' => env('MP_CLIENT_ACCESS_TOKEN'),
    ],
];