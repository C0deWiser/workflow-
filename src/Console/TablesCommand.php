<?php

namespace Media101\Workflow\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Illuminate\Filesystem\Filesystem;

class TablesCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'workflow:tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a migration for the workflow database tables';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * Create a new queue job table command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  \Illuminate\Support\Composer    $composer
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();

        $this->files = $files;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $filePath = app('migration.creator')
            ->create('create_workflow_tables', app()->databasePath() . '/migrations');

        $contents = strtr($this->files->get(__DIR__ . '/stubs/tables.stub'), [
            '{{usersTable}}' => config('workflow.database.users_table'),
            '{{entitiesTable}}' => config('workflow.database.entities_table'),
            '{{actionsTable}}' => config('workflow.database.actions_table'),
            '{{relationsTable}}' => config('workflow.database.relations_table'),
            '{{featuresTable}}' => config('workflow.database.features_table'),
            '{{statesTable}}' => config('workflow.database.states_table'),
            '{{rolesTable}}' => config('workflow.database.roles_table'),
            '{{permissionsTable}}' => config('workflow.database.permissions_table'),
            '{{roleUserTable}}' => config('workflow.database.role_user_table'),
        ]);

        $this->files->put($filePath, $contents);

        $this->info('Migration created successfully!');

        $this->composer->dumpAutoloads();
    }
}
