<?php

namespace Michaelgatuma\Kopokopo;

use Illuminate\Support\ServiceProvider;
use Michaelgatuma\Kopokopo\Console\InstallKopokopo;

class KopokopoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/kopokopo.php', 'kopokopo');

        $this->app->bind('Kopokopo',function (){
            return new Kopokopo();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        include __DIR__.'/routes.php';
//        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

        if ($this->app->runningInConsole()) {
            //publish the config files
            $this->publishes([
                __DIR__.'/../config/kopokopo.php' => config_path('kopokopo.php'),
            ], 'kopokopo-config');

            // Register commands
            $this->commands([
                InstallKopokopo::class,
            ]);
        }

    }
}
