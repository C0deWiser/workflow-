<?php

namespace Codewiser\Workflow;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Codewiser\Workflow\Contracts\PermissionsStorage as PermissionsStorageContract;
use Codewiser\Workflow\Contracts\Workflow as WorkflowContract;
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
                return app(Guard::class)->user();
            });
        });
        $this->app->singleton(Gate::class, WorkflowContract::class);
    }

    /**
     * @inherit
     */
    public function provides()
    {
        return [ Gate::class, WorkflowContract::class, PermissionsStorageContract::class ];
    }
}
