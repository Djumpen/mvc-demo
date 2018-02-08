<?php

return [

    'db' => [
        'database_type' => 'mysql',
        'database_name' => 'dtapp',
        'server'        => 'localhost',
        'username'      => 'root',
        'password'      => 'root',
        'port'          => '3303'
    ],

    'template_defaults' => [
        'project_name'  => 'MVCApp Demo',
        'project_title' => 'MyDemo'
    ],

    'app' => [
        'uploads_dir'  => ROOT . '/uploads/',
        'uploads_path'   => '/uploads/',
        'no_image'      => '/img/no_image.png'
    ]

];