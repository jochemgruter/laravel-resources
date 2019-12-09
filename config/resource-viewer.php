<?php

return [

    'route' => '/',

    'middleware' => ['web', 'auth'],

    'layout' => [

        'view' => 'ResourceViewer::exampleLayout',

        'section' => 'content'
    ],

    'index' => [
        'rows' => 25,

        'available' => [25, 50, 100, 250, 500]
    ]
];