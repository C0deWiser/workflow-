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
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    public $defer = true;

    /**
     * Bootstrap the module
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
            dirname(__DIR__) . '/config/workflow.php' => config_path('workflow.php'),
        ]);
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
     * @inherit
     */
    public function provides()
    {
        return [ WorkflowContract::class, PermissionsStorageContract::class ];
    }
}
