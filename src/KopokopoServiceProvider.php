<?php

namespace Michaelgatuma\Kopokopo;

use Illuminate\Support\ServiceProvider;

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
        //boot routes,event listeners etc
        include __DIR__.'/routes.php';
//        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

    }
}
