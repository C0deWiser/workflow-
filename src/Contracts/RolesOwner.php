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
     * @return Role[]|Collection
     */
    public function getRoles();
}
