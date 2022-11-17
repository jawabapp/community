<?php

namespace Jawabapp\Community;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Builder;

class CommunityServiceProvider extends ServiceProvider
{

    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Jawabapp\Community\Http\Controllers';

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'community');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'community');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->registerRoutes();

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('community.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/community'),
            ], 'views');*/

            // Publishing assets.
            $this->publishes([
                __DIR__ . '/../public' => public_path('vendor/community'),
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
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'community');

        $this->app->register(EventServiceProvider::class);

        // Register the main class to use with the facade
        $this->app->singleton('community', function () {
            return new CommunityService;
        });

        foreach (config('community.relations', []) as $class => $relations) {
            foreach ($relations as $relation_name => $relation_callback) {
                if (method_exists($class, 'addDynamicRelation')) {
                    $class::addDynamicRelation($relation_name, $relation_callback);
                }
            }
        }

        foreach (config('community.with', []) as $class => $withs) {
            foreach ($withs as $with_name => $with_callback) {
                $with_name = is_callable($with_callback) ? $with_name : $with_callback;
                if (is_string($with_name)) {
                    $class::addGlobalScope('with_' . $with_name, function (Builder $builder) use ($with_name, $with_callback) {
                        if (is_callable($with_callback)) {
                            $builder->with([$with_name => $with_callback]);
                        } else {
                            $builder->with($with_name);
                        }
                    });
                }
            }
        }

        foreach (config('community.appends', []) as $class => $appends) {
            foreach ($appends as $append_name => $append_callback) {
                if (method_exists($class, 'addDynamicAppend')) {
                    $class::addDynamicAppend($append_name, $append_callback);
                }
            }
        }

        foreach (config('community.hidden', []) as $class => $hidden) {
            foreach ($hidden as $hidden_attr) {
                if (method_exists($class, 'addDynamicHidden')) {
                    $class::addDynamicHidden($hidden_attr);
                }
            }
        }
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        $this->mapWebRoutes();

        $this->mapApiRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::prefix(config('community.route.prefix'))
            ->middleware(config('community.route.middleware', 'web'))
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../routes/web.php');
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(__DIR__ . '/../routes/api.php');
    }
}
