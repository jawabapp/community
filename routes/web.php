<?php

use Illuminate\Support\Facades\Route;

Route::get('test', 'TestController@test')->name('jawab.community.test');

Route::resource('posts', 'Web\Admin\PostsController', [
    'names' => [
        'index' => 'community.posts.index',
        'create' => 'community.posts.create',
        'update' => 'community.posts.update',
        'edit' => 'community.posts.edit',
        'destroy' => 'community.posts.destroy',
        'store' => 'community.posts.store',
        'show' => 'community.posts.show',
    ]
]);
