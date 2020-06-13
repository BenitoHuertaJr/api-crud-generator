<?php

namespace iamx\ApiCrudGenerator;

use Illuminate\Support\ServiceProvider;
use iamx\ApiCrudGenerator\Commands\CrudGeneratorCommand;

class ApiCrudGeneratorProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        /*
         * Register the service provider for the dependency.
         */
        $this->app->register('ApiCrudGenerator\ApiCrudGeneratorProvider');
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
