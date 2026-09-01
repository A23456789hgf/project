<?php

return [

    'erpnext' => [
        'base_url' => env('ERP_BASE_URL', env('FRAPPE_URL')),
        'auth' => env('ERP_AUTH_TYPE', 'basic'), // token | basic
        'user' => env('ERP_USER', env('FRAPPE_API_KEY')),
        'pass' => env('ERP_PASS', env('FRAPPE_API_SECRET')),
        'timeout' => 30,
    ],

];
