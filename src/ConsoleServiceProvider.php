<?php

namespace Media101\Workflow;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Media101\Workflow\Console\TablesCommand;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->singleton('command.workflow.tables', function (Application $app) {
            return new TablesCommand($app->make('files'), $app->make('composer'));
        });

        $this->commands('command.workflow.tables');
    }

    /**
     * @inheritdoc
     */
    public function provides()
    {
        return [ 'command.workflow.tables' ];
    }
}
