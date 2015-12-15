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

    /**
     * Return array containing criteria to be fulfilled to be allowed for specified action
     *
     * @param string $action
     * @param Entity $entity
     * @return array
     */
    public function permissionsFor($action, $entity);
}
