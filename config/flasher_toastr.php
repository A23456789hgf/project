<?php

/*
 * This file is part of the PHPFlasher package.
 * (c) Younes KHOUBZA <younes.khoubza@gmail.com>
 */

return [
    'scripts' => [
        'cdn' => [
            'https://cdn.jsdelivr.net/npm/jquery@3.6.3/dist/jquery.min.js',
            'https://cdn.jsdelivr.net/npm/@flasher/flasher-toastr@1.3.2/dist/flasher-toastr.min.js',
        ],
        'local' => [
            '/vendor/flasher/jquery.min.js',
            '/vendor/flasher/flasher-toastr.min.js',
        ],
    ],
    'styles' => [
        'cdn' => [
            'https://cdn.jsdelivr.net/npm/@flasher/flasher-toastr@1.3.2/dist/flasher-toastr.min.css',
        ],
        'local' => [
            '/vendor/flasher/flasher-toastr.min.css',
            '/css/toastr-custom.css',
        ],
    ],
    'options' => [
        'positionClass' => 'toast-bottom-left',
        'rtl' => true,
    ],
];
