<?php

namespace DevsNG\Interswitch;

use Illuminate\Support\ServiceProvider;

class InterswitchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $path = realpath(__DIR__.'/../config/interswitch.php');

        $this->publishes([
            $path => config_path('interswitch.php')
        ]);

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->bind('interswitch', function() {
          return new Interswitch;
        });

    }

}
