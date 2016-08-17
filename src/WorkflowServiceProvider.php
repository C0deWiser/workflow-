<?php

namespace Media101\Workflow;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Media101\Workflow\Contracts\PermissionsStorage as PermissionsStorageContract;
use Media101\Workflow\Contracts\Workflow as WorkflowContract;
use Illuminate\Contracts\Auth\Access\Gate;

class WorkflowServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the module
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
            dirname(__DIR__) . '/config/workflow.php' => config_path('workflow.php'),
        ]);
        $this->extendValidator();
    }

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/workflow.php', 'workflow');
        $this->registerPermissionsStorage();
        $this->registerWorkflow();
        $this->registerAliases();
    }

    private function extendValidator()
    {
        $app = $this->app;
        $app->make('validator')->extend('can', function ($attribute, $value, $parameters, $validator) use ($app) {
            $class = $parameters[0];
            if (!($entity = $class::find($value))) {
                return false;
            }
            return $app->make(WorkflowContract::class)->allows($parameters[1], $entity);
        });
    }

    /**
     * Register utility service
     */
    private function registerPermissionsStorage()
    {
        $this->app->singleton(PermissionsStorageContract::class, PermissionsStorage::class);
    }

    /**
     * Register main service
     */
    protected function registerWorkflow()
    {
        $this->app->singleton(WorkflowContract::class, function(Application $app) {
            return new Workflow($app, function() use($app) {
                return app(Guard::class)->user() ?: "guest";
            });
        });
        $this->app->singleton(Gate::class, WorkflowContract::class);
    }

    /**
     * Register aliases.
     */
    private function registerAliases()
    {
        $this->app->alias('Form', \Collective\Html\FormFacade::class);
    }

    /**
     * @inherit
     */
    public function provides()
    {
        return [ Gate::class, WorkflowContract::class, PermissionsStorageContract::class ];
    }
}
