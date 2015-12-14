<?php

namespace Media101\Workflow\Contracts;

use Media101\Workflow\Models\Entity;

/**
 * Class stores raw permissions and should not be directly used, Workflow gate should be preferred.
 *
 * @package Media101\Workflow\Contracts
 */
interface PermissionsStorage
{
    /**
     * Returns entity by the name. All properties like actions and the rest will be preloaded.
     *
     * @param string $name
     *
     * @return Entity
     */
    public function entity($name);
}
