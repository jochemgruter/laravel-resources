<?php

return [

    'route' => '/',

    'middleware' => ['web', 'auth:employee'],

    'layout' => [

        'view' => 'layouts.main',

        'section' => 'content'
    ],

    'index' => [
        'rows' => 25,

        'available' => [25, 50, 100, 250, 500]
    ]
];