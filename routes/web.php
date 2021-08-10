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

Route::get('tag-groups/tags', 'Web\Admin\TagGroupsController@tags')->name('community.tag-groups.tags');


Route::resource('tag-groups', 'Web\Admin\TagGroupsController', [
    'names' => [
        'index' => 'community.tag-groups.index',
        'create' => 'community.tag-groups.create',
        'update' => 'community.tag-groups.update',
        'edit' => 'community.tag-groups.edit',
        'destroy' => 'community.tag-groups.destroy',
        'store' => 'community.tag-groups.store',
        'show' => 'community.tag-groups.show'
    ]
]);


Route::group(['prefix' => 'admin'], function () {
    Route::group(['prefix' => 'api'], function () {
        // Route::post('target-audience', 'Api\Admin\IndexController@targetAudience');
        // Route::get('countries', 'Api\Admin\IndexController@countries');
        // Route::get('tags', 'Api\Admin\IndexController@tags');
        // Route::get('tag-groups', 'Api\Admin\IndexController@tagGroups');
        // Route::get('services', 'Api\Admin\IndexController@services');
        // Route::get('languages', 'Api\Admin\IndexController@languages');
        // Route::get('registers', 'Api\Admin\IndexController@registers');
        // Route::get('parse', 'Api\Admin\IndexController@parse');

        Route::get('search-tags', 'Api\Admin\IndexController@searchTags');
        Route::post('selected-tags', 'Api\Admin\IndexController@selectedTags');

        Route::get('search-services', 'Api\Admin\IndexController@searchServices');
        Route::post('selected-services', 'Api\Admin\IndexController@selectedServices');

        Route::post('assign-tag-group', 'Web\Admin\TagGroupsController@assign');

        // Route::group(['prefix' => 'upload'], function() {
        //     Route::post('image', 'Api\Admin\IndexController@image');
        // });
    });
});
