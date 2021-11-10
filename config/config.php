<?php

use Jawabapp\Community\Models;

/*
 * You can place your custom package configuration in here.
 */

return [
    'user_class' => \App\Models\User::class,
    'slug_fields' => ['name'],
    'search_fields' => ['name'],
    'check_anonymous' => true,
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
            'url_prefix' => null,
            'utm_source' => null,
            'utm_medium' => null,
            'utm_campaign' => null,
        ],
        'post' => [
            'url_prefix' => null,
            'utm_source' => null,
            'utm_medium' => null,
            'utm_campaign' => null,
        ],
        'tag' => [
            'url_prefix' => null,
            'utm_source' => null,
            'utm_medium' => null,
            'utm_campaign' => null,
        ],
        'hashtag' => [
            'url_prefix' => null,
            'utm_source' => null,
            'utm_medium' => null,
            'utm_campaign' => null,
        ]
    ],
    'listeners' => [
        'post_reply' => null,
        'post_interaction' => null,
        'post_mention' => null
    ],
    'mimetypes' =>  [
        'gif' => 'image/gif',
        'image' => 'image/jpeg,image/png,image/webp,image/gif,image/svg+xml',
        'video' => 'video/mp4,video/3gpp,video/quicktime,video/x-msvideo,video/x-ms-wmv',
        'audio' => 'audio/mid,audio/mpeg,audio/mp4,audio/vnd.wav,audio/x-aiff,audio/aac,audio/3gpp,audio/x-hx-aac-adts,audio/x-m4a'
    ]

];
