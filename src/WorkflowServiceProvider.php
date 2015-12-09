<?php

namespace Media101\Workflow;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

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
        $this->registerWorkflow();
        $this->app->singleton(Preloader::class);
    }

    /**
     * Register main service
     */
    protected function registerWorkflow()
    {
        $this->app->singleton(Workflow::class, function(Application $app) {
            return new Workflow($app, function() use($app) {
                return \Auth::user() ?: "guest";
            });
        });
    }

    /**
     * @inherit
     */
    public function provides()
    {
        return [ Workflow::class, Preloader::class ];
    }
}
