<?php

namespace Media101\Workflow\Console;

use Illuminate\Console\Command;
use Media101\Workflow\Models\Role;
use App\User;
use Media101\Workflow\Contracts\RolesOwner;
use Media101\Workflow\Contracts\PermissionsStorage;
use Media101\Workflow\Contracts\WorkflowItem;
use Illuminate\Database\Connection;

class AdminCommand extends Command
{
    /**
     * The console command name and signature
     *
     * @var string
     */
    protected $signature = 'workflow:admin
                            {roleName=admin : The name of the role created}
                            {--N|no-assign : Skip assigning the role to the first user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates admin role (user with all permissions). Possibly assigns this role to a user.';

    public function handle(PermissionsStorage $storage, Connection $db)
    {
        $role = $this->createRole($this->argument('roleName'));
        $this->info('Role "' . $role->code . '" created.');

        $this->assignAllPermissions($role, $storage, $db);
        $this->info('All permissions has been assigned to the role "' . $role->code . '"');


        if (!$this->option('no-assign')) {
            $user = User::findOrFail(1);
            /* @var $user RolesOwner */
            $user->addRole($role);
            $this->info('Role attached to the user "' . $user . '"');
        }

        $this->call('workflow:reinit');
    }

    private function createRole($name)
    {
        $role = new Role();
        $role->code = $name;
        $role->saveOrFail();
        return $role;
    }

    private function assignAllPermissions($role, PermissionsStorage $storage, Connection $db)
    {
        $permissions = [];
        foreach (config('workflow.classes') as $class) {
            /* @var WorkflowItem $class */
            $entity = $storage->entity($class::workflowName());
            foreach ($entity->actions as $action) {
                $permissions[] = [
                    'entity_id' => $entity->id,
                    'action_id' => $action->id,
                    'role_id' => $role->id,
                    'state_id' => null,
                ];
            }
            foreach ($entity->states as $state) {
                $permissions[] = [
                    'entity_id' => $entity->id,
                    'action_id' => null,
                    'role_id' => $role->id,
                    'state_id' => $state->id,
                ];
            }
        }
        $db->table(config('workflow.database.permissions_table'))->insert($permissions);
    }
}
