<?php

use Jawabapp\Community\Models;

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
//        Models\Tag::class => [
//            'doa' => function (Models\Tag $model) {
//                return $model->hasOne(DoaExperience::class, 'hash_tag', 'hash_tag');
//            }
//        ],
//        Models\Post::class => [
//            'tags_doa' => function (Models\Post $model) {
//                return $model->tags()->with('doa')->join('doa_experiences', 'tags.hash_tag', '=', 'doa_experiences.hash_tag')->latest();
//            }
//        ],
    ],
    'with' => [
//        Models\Post::class => ['tags_doa'],
    ],
    'appends' => [
//        Models\Post::class => [
//            'tag_doa' => function(Models\Post $model) {
//                return $model->tags_doa->first();
//            },
//        ],
    ],
    'deep_link' => [
        'class' => null,
        'action' => 'generate',
        'account' => [
            'url' => null,
            'utm_source' => null,
            'utm_medium' => null,
            'utm_campaign' => null,
        ],
        'post' => [
            'url' => null,
            'utm_source' => null,
            'utm_medium' => null,
            'utm_campaign' => null,
        ],
        'tag' => [
            'url' => null,
            'utm_source' => null,
            'utm_medium' => null,
            'utm_campaign' => null,
        ]
    ]
];
