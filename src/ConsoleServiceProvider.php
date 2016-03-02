<?php

namespace Media101\Workflow;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Media101\Workflow\Console\ReinitCommand;
use Media101\Workflow\Console\TablesCommand;
use Media101\Workflow\Console\AdminCommand;

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
        $this->app->singleton('command.workflow.reinit', ReinitCommand::class);
        $this->app->singleton('command.workflow.admin', AdminCommand::class);

        $this->commands('command.workflow.tables', 'command.workflow.reinit', 'command.workflow.admin');
    }

    /**
     * @inheritdoc
     */
    public function provides()
    {
        return [ 'command.workflow.tables', 'command.workflow.reinit', 'command.workflow.admin' ];
    }
}
