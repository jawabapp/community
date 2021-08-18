<?php

use Jawabapp\Community\Models\Tag;

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
        Tag::class => [
            'user' => function (Tag $model) {}
        ],
    ],
    'with' => [
        Tag::class => ['doa']
    ]
];
