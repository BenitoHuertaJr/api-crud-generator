<?php

namespace iamx\ApiCrudGenerator;

use Illuminate\Support\ServiceProvider;
use iamx\ApiCrudGenerator\Commands\CrudGeneratorCommand;

class ApiCrudGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CrudGeneratorCommand::class,
            ]);
        }
    }
}
