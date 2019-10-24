<?php

namespace Codewiser\Workflow;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Codewiser\Workflow\Console\ReinitCommand;
use Codewiser\Workflow\Console\TablesCommand;
use Codewiser\Workflow\Console\AdminCommand;

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
