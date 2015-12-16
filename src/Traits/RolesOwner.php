<?php

namespace Media101\Workflow\Traits;

use Illuminate\Database\Eloquent\Collection;
use Media101\Workflow\Models\Role;

/**
 * Default implementation for contract
 *
 * @property Role[]|Collection $roles
 *
 * @package Media101\Workflow\Traits
 */
trait RolesOwner
{
    public function roles()
    {
        /* @var $this \Illuminate\Database\Eloquent\Model */
        return $this->belongsToMany(Role::class, 'workflow_user_role', 'user_id', 'role_id');
    }

    public function getRoles()
    {
        return $this->roles;
    }
}
