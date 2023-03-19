<?php

namespace LarawireGarage\SimpleMultitenancy;

use Illuminate\Support\ServiceProvider;
use LarawireGarage\SimpleMultitenancy\Macros\BlueprintCustomMacros;

class SimpleMultitenancyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../configs/simple-mutitenancy.php', 'simple-mutitenancy');

        $this->registerMacros();

        if ($this->app->runningInConsole()) {
            $this->addConfigs();
        }
    }

    public function registerMacros()
    {
        (new BlueprintCustomMacros)();
    }

    public function addConfigs()
    {
        // add configs
        $this->publishes([
            __DIR__ . '/../configs/simple-mutitenancy.php' => config_path('simple-mutitenancy.php'),
        ], 'simple-mutitenancy-configs');
    }
}
