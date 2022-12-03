<?php

namespace Michaelgatuma\Kopokopo;

use Illuminate\Support\ServiceProvider;

class KopokopoServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'michaelgatuma');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'michaelgatuma');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/kopokopo.php', 'kopokopo');

        // Register the service the package provides.
        $this->app->bind('kopokopo', function ($app) {
            return new Kopokopo;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['kopokopo'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/kopokopo.php' => config_path('kopokopo.php'),
        ], 'kopokopo.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/michaelgatuma'),
        ], 'kopokopo.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/michaelgatuma'),
        ], 'kopokopo.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/michaelgatuma'),
        ], 'kopokopo.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
