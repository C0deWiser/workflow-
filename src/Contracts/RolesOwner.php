<?php

namespace Codewiser\Workflow\Contracts;

use Illuminate\Support\Collection;
use Codewiser\Workflow\Models\Role;

/**
 * Class for users which can be assigned roles
 *
 * @package Codewiser\Workflow\Contracts
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
