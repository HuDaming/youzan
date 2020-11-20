<?php

namespace Hudm\Youzan;

use Illuminate\Support\ServiceProvider;

class YouzanServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Youzan::class, function ($app) {
            return new Youzan(config('youzan'));
        });
    }

    public function provides()
    {
        return [Youzan::class, 'youzan'];
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/youzan.php' => config_path('youzan.php')
        ]);
    }
}
