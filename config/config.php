<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'account_class' => MobileUser::class,
    'route' => [
        'prefix' => null,
        'middleware' => 'web',
    ]
];
