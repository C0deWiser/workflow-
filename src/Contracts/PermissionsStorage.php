<?php

namespace Codewiser\Workflow\Contracts;

use Codewiser\Workflow\Models\Entity;

/**
 * Class stores raw permissions and should not be directly used, Workflow gate should be preferred.
 *
 * @package Codewiser\Workflow\Contracts
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

    /**
     * Return associative array indexed by transition (state) name contains criteria to be fulfilled to be allowed
     * to perform the transition.
     *
     * @param Entity $entity
     * @return array
     */
    public function transitionsFor($entity);
}
