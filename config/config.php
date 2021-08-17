<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'user_class' => \App\Models\User::class,
    'route' => [
        'prefix' => null,
        'middleware' => 'web',
    ],
    'relations' => [
        \Jawabapp\Community\Models\Tag::class => [
            'user' => function () {

            }
        ],
    ],
    'with' => [
        'tag' => [
            'user'
        ],
    ]
];
