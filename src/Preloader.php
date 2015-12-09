<?php

namespace Media101\Workflow;

use Media101\Workflow\Models\Entity;

/**
 * Service keeps some workflow data alive in memory to avoid repetitive requests to the database.
 *
 * @package Media101\Workflow
 */
class Preloader
{
    protected $loaded = false;

    /**
     * @var Entity[]
     */
    protected $entities = [];

    protected function load()
    {
        if ($this->loaded) {
            return;
        }

        $entities = Entity::with('states', 'relations', 'features', 'actions')->get()->keyBy('code');
        $this->entities = $entities->all();

        $this->loaded = true;
    }

    /**
     * Loads entity object for the given class (by name).
     *
     * @param string $name Workflow name of the entity
     * @return Entity
     */
    public function entity($name)
    {
        $this->load();
        return $this->entities[$name];
    }
}
