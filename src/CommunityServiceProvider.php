<?php

namespace Jawabapp\Community;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class CommunityServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'community');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'community');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->registerRoutes();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('community.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/community'),
            ], 'views');*/

            // Publishing assets.
            $this->publishes([
                __DIR__.'/../public' => public_path('vendor/community'),
            ], 'assets');

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/community'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'community');

        // Register the main class to use with the facade
        $this->app->singleton('community', function () {
            return new Community;
        });
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    /**
     * Get the Telescope route group configuration array.
     *
     * @return array
     */
    private function routeConfiguration()
    {
        return [
            'namespace' => 'Jawabapp\Community\Http\Controllers',
            'prefix' => config('community.route.prefix'),
            'middleware' => config('community.route.middleware'),
        ];
    }
}
