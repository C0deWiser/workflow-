<?php

namespace Media101\Workflow;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Connection;
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
            foreach ($value as $actionCode => $permissions) {
                $this->permissions[$entity->code][$actionCode] = $permissions;
            }
            return $value[$action];
        }

        $actionsPermissions = [];
        $actions = $entity->actions->pluck('code', 'id');
        foreach ($actions as $actionCode) {
            $actionsPermissions[$actionCode] = [];
        }

        $builder = app(Connection::class)->table(config('workflow.database.permissions_table'))
            ->where([
                'entity_id' => $entity->id,
            ]);
        foreach ($builder->get() as $row) {
            $action_id = $row['action_id'];
            /* @var string $action_id */
            $actionsPermissions[$actions[$action_id]][] = [
                'states' => isset($row['state_id']) ? [ $row['state_id'] ] : null,
                'relations' => isset($row['relation_id']) ? [ $row['relation_id'] ] : null,
                'roles' => isset($row['role_id']) ? [ $row['role_id'] ] : null,
                'features' => isset($row['feature_id']) ? [ $row['feature_id'] ] : null,
            ];
        }

        foreach ($actionsPermissions as $actionCode => $permissions) {
            $this->permissions[$entity->code][$actionCode] = $permissions;
        }
        $this->cache->forever($cacheKey, $actionsPermissions);
        return $actionsPermissions[$action];
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
