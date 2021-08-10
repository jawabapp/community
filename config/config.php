<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'user_class' => \App\Models\User::class,
    'country_code_field_name' => 'country_code',
    'route' => [
        'prefix' => null,
        'middleware' => 'web',
    ]
];
