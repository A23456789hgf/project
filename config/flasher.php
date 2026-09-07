<?php

return [

    'default' => 'flasher',

    'options' => [
        'position' => 'bottom-left',
        'timeout' => 5000,
        'rtl' => true,
    ],

    'root_script' => [
        'cdn' => 'https://cdn.jsdelivr.net/npm/@flasher/flasher@1.3.2/dist/flasher.min.js',
        'local' => '/vendor/flasher/flasher.min.js',
    ],

    'styles' => [
        'cdn' => 'https://cdn.jsdelivr.net/npm/@flasher/flasher@1.3.2/dist/flasher.min.css',
        'local' => '/vendor/flasher/flasher.min.css',
    ],

    'use_cdn' => false,

    'auto_translate' => true,

    'auto_render' => true,

    'flash_bag' => [
        'success' => ['success'],
        'error' => ['error', 'danger'],
        'warning' => ['warning'],
        'info' => ['info'],
    ],

    'filter_criteria' => [
        'limit' => 5,
    ],
];
