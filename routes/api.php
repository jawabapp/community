<?php

use Illuminate\Support\Facades\Route;

Route::get('test', 'TestController@testApi');

Route::group(['prefix' => 'account'], function () {
    Route::get('show/{slug}', 'Api\Account\ShowController@index');

    Route::group(['prefix' => 'follow'], function () {
        Route::get('followers/{accountId}', 'Api\Account\Follow\FollowersController@index');
        Route::get('following/{accountId}', 'Api\Account\Follow\FollowingController@index');
    });

    Route::group(['middleware' => 'auth:api'], function () {
        Route::group(['prefix' => 'follow'], function () {
            Route::post('/follow/{accountId}', 'Api\Account\Follow\FollowController@index');
            Route::post('/un-follow/{accountId}', 'Api\Account\Follow\UnFollowController@index');
            Route::get('/mutual/{accountId}', 'Api\Account\Follow\MutualController@index');
        });

        Route::post('/block/{accountId}', 'Api\Account\Block\BlockController@index');
        Route::post('/unblock/{accountId}', 'Api\Account\Block\UnblockController@index');
    });
});

Route::group(['prefix' => 'community'], function () {

    Route::get('search', 'Api\Community\SearchController@index');
    Route::get('search/posts', 'Api\Community\SearchController@posts');
    Route::get('search/accounts', 'Api\Community\SearchController@accounts');
    Route::get('search/tags', 'Api\Community\SearchController@tags');

    Route::group(['prefix' => 'post'], function () {
        Route::get('list', 'Api\Community\Post\ListController@index');
        Route::get('show/{id}', 'Api\Community\Post\ShowController@index');
    });

    Route::group(['prefix' => 'hash-tag'], function () {
        Route::get('info', 'Api\Community\HashTag\InfoController@index');
        Route::get('post-list', 'Api\Community\HashTag\PostListController@index');
        Route::group(['prefix' => 'follow'], function () {
            Route::get('following/{accountId}', 'Api\Community\HashTag\Follow\FollowingController@index');
        });
    });

    // Route::group(['middleware' => 'auth:api'], function () {

        Route::get('subscribe-notification/{type}/{id}/{account_id}', 'Api\Community\NotificationController@subscribe');
        Route::get('unsubscribe-notification/{type}/{id}/{account_id}', 'Api\Community\NotificationController@unSubscribe');

        Route::group(['prefix' => 'post'], function () {
            Route::post('/create', 'Api\Community\Post\CreateController@create');
            Route::post('/edit/{id}', 'Api\Community\Post\EditController@edit');
            Route::post('/delete/{id}', 'Api\Community\Post\DeleteController@index');
            Route::post('/interaction/{id}', 'Api\Community\Post\InteractionController@index');
            Route::get('/interaction/list/{id}', 'Api\Community\Post\InteractionListController@index');
            Route::get('/report/types', 'Api\Community\Post\ReportController@index');
            Route::post('/report/{id}', 'Api\Community\Post\ReportController@report');
            Route::get('/related/{id}', 'Api\Community\Post\RelatedController@index');
        });
        Route::group(['prefix' => 'hash-tag'], function () {
            Route::group(['prefix' => 'follow'], function () {
                Route::post('follow/{accountId}', 'Api\Community\HashTag\Follow\FollowController@index');
                Route::post('un-follow/{accountId}', 'Api\Community\HashTag\Follow\UnFollowController@index');
            });
            Route::group(['prefix' => 'group'], function () {
                Route::get('list', 'Api\Community\HashTag\Group\ListController@index');
                Route::post('follow/{accountId}', 'Api\Community\HashTag\Group\ListController@follow');
            });
        });
    // });
});
