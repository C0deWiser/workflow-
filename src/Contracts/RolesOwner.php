<?php

namespace Media101\Workflow\Contracts;

use Illuminate\Support\Collection;
use Media101\Workflow\Models\Role;

/**
 * Class for users which can be assigned roles
 *
 * @package Media101\Workflow\Contracts
 */
interface RolesOwner
{
    /**
     * Returns all the roles assigned to this user
     * @return Role[]|Collection
     */
    public function getRoles();

    /**
     * Adds a role (or roles) to the user
     * @param Role|Role[]|Collection
     */
    public function addRole($role);

    /**
     * Disassigns all the role from the user
     */
    public function clearRoles();
}
