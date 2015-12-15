<?php

namespace Media101\Workflow;

use Illuminate\Contracts\Cache\Repository;
use Media101\Workflow\Contracts\PermissionsStorage as PermissionsStorageContract;
use Media101\Workflow\Models\Entity;

/**
 * Service keeps some workflow data alive in memory to avoid repetitive requests to the database.
 *
 * @package Media101\Workflow
 */
class PermissionsStorage implements PermissionsStorageContract
{
    /**
     * @var Repository
     */
    protected $cache;

    protected $cacheKey = 'permissions-storage';

    protected $permissions = [];

    protected $loaded = false;

    /**
     * @var Entity[]
     */
    protected $entities = [];

    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function entity($name)
    {
        $this->load();
        return $this->entities[$name];
    }

    /**
     * @inheritdoc
     */
    public function permissionsFor($action, $entity)
    {
        if (isset($this->permissions[$entity->code][$action])) {
            return $this->permissions[$entity->code][$action];
        }

        $cacheKey = $this->cacheKey . '-' . $entity->code;
        if (($value = $this->cache->get($cacheKey)) !== null) {
            return $value;
        }

        // todo fill here
        $permissions = [];

        $this->permissions[$entity->code][$action] = $permissions;
        $this->cache->forever($cacheKey, $permissions);
        return $permissions;
    }

    protected function load()
    {
        if ($this->loaded) {
            return;
        }

        $entities = Entity::with('states', 'relations', 'features', 'actions')->get()->keyBy('code');
        $this->entities = $entities->all();

        $this->loaded = true;
    }
}
