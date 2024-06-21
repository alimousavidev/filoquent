<?php

namespace AliMousavi\Filoquent;

use AliMousavi\Filoquent\Commands\MakeFilter;
use Illuminate\Support\ServiceProvider;

class FiloquentServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeFilter::class,
            ]);
        }
    }

    public function register()
    {

    }
}
