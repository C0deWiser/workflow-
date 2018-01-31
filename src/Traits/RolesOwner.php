<?php

namespace Media101\Workflow\Traits;

use Illuminate\Support\Collection;
use Media101\Workflow\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Default implementation for contract
 *
 * @property Role[]|Collection $roles
 *
 * @package Media101\Workflow\Traits
 */
trait RolesOwner
{
    /**
     * @return BelongsToMany|Role[]|Collection
     */
    public function roles()
    {
        /* @var $this \Illuminate\Database\Eloquent\Model */
        return $this->belongsToMany(Role::class, config('workflow.database.role_user_table'), 'user_id', 'role_id');
    }

    /**
     * @return Role[]|Collection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param Role|Role[]|Collection $roles
     */
    public function addRole($roles)
    {
        $this->roles()->attach($roles);
    }

    public function clearRoles()
    {
        $this->roles()->detach();
    }
}
