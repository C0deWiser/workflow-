<?php

namespace Codewiser\Workflow;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Connection;
use Codewiser\Workflow\Contracts\PermissionsStorage as PermissionsStorageContract;
use Codewiser\Workflow\Models\Entity;

/**
 * Service keeps some workflow data alive in memory to avoid repetitive requests to the database.
 *
 * @package Codewiser\Workflow
 */
class PermissionsStorage implements PermissionsStorageContract
{
    /**
     * @var Connection
     */
    protected $db;

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

    public function __construct(Repository $cache, Connection $db)
    {
        $this->cache = $cache;
        $this->db = $db;
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

        // Attempt to retrieve from cache, where we store all the permissions per entity
        $cacheKey = $this->cacheKey . '-' . $entity->code;
        if (($value = $this->cache->get($cacheKey)) !== null) {
            foreach ($value as $actionCode => $permissions) {
                $this->permissions[$entity->code][$actionCode] = $permissions;
            }
            return $value[$action];
        }

        $actionsPermissions = [];
        $actions = $entity->actions->pluck('code', 'id');
        $states = $entity->states->pluck('code', 'id');
        foreach ($actions->merge($states) as $actionCode) {
            $actionsPermissions[$actionCode] = [];
        }

        $builder = $this->db->table(config('workflow.database.permissions_table'))
            ->where([
                'entity_id' => $entity->id,
            ]);
        foreach ($builder->get() as $row) {
            $code = isset($row->action_id) ? $actions[$row->action_id] : $states[$row->target_state_id];
            $actionsPermissions[$code][] = [
                'states' => isset($row->state_id) ? [ $row->state_id ] : null,
                'relations' => isset($row->relation_id) ? [ $row->relation_id ] : null,
                'roles' => isset($row->role_id) ? [ $row->role_id ] : null,
                'features' => isset($row->feature_id) ? [ $row->feature_id ] : null,
                'authenticated' => $row->authenticated,
            ];
        }

        foreach ($actionsPermissions as $actionCode => $permissions) {
            $this->permissions[$entity->code][$actionCode] = $permissions;
        }
        $this->cache->forever($cacheKey, $actionsPermissions);
        return $actionsPermissions[$action];
    }

    public function transitionsFor($entity)
    {
        $transitions = [];
        foreach ($entity->states as $state) {
            $transitions[$state->code] = $this->permissionsFor($state->code, $entity);
        }
        return $transitions;
    }

    protected function load()
    {
        if ($this->loaded) {
            return;
        }

        if (($entities = $this->cache->get($this->cacheKey)) !== null) {
            $this->entities = $entities;
            $this->loaded = true;
            return;
        }

        $entities = Entity::with('states', 'relationships', 'features', 'actions')->get()->keyBy('code');
        $this->cache->forever($this->cacheKey, $this->entities = $entities->all());

        $this->loaded = true;
    }
}
