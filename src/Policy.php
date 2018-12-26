<?php

namespace Media101\Workflow;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Media101\Workflow\Contracts\PermissionsStorage as PermissionsStorageContract;
use Media101\Workflow\Contracts\RolesOwner;
use Media101\Workflow\Contracts\WorkflowItem;
use Media101\Workflow\Models\Feature;
use Media101\Workflow\Models\Relation;
use Media101\Workflow\Models\Role;
use Media101\Workflow\Models\State;

/**
 * Workflow policy allows action (ability) on item if there is a permission written in the permissions storage
 * which references entity class of this item, action referring to the checked one, with no state specified
 * or the state equal to the state of the item, null relations reference (which mean any relation)
 * or one of the relations the item currently has with the user, null feature or one of the features of the current item
 * and null role or one of the roles of the user.
 *
 * So, if any of such allowing records are in the permissions table - access will be granted, and denied otherwise.
 *
 * @package Media101\Workflow
 */
class Policy
{
    /**
     * @var PermissionsStorageContract
     */
    protected $permissions;

    /**
     * @var Str
     */
    protected $str;

    public function __construct(PermissionsStorageContract $permissions, Str $str)
    {
        $this->permissions = $permissions;
        $this->str = $str;
    }

    public function __call($name, $arguments = [])
    {
        $user = is_object($arguments[0]) ? $arguments[0] : null;
        $item = $arguments[1];
        return $this->checkAccess($name, $item, $user);
    }

    /**
     * Filter the eloquent query leaving only items on which certain action can be performed.
     *
     * @param string $action
     * @param EloquentBuilder $queryBuilder
     * @param Authenticatable|null $user
     * @return EloquentBuilder
     */
    public function filter($action, EloquentBuilder $queryBuilder, Authenticatable $user = null)
    {
        return $this->filterQuery($action, $queryBuilder, $user);
    }

    /**
     * Filter the eloquent query leaving only items on which certain action CANNOT be performed.
     *
     * @param string $action
     * @param EloquentBuilder $queryBuilder
     * @param Authenticatable|null $user
     * @return EloquentBuilder
     * @throws \Exception
     */
    public function except($action, EloquentBuilder $queryBuilder, Authenticatable $user = null)
    {
        throw new \Exception('Method Policy::except is not yet implemented. Why would you need it anyway?');
    }

    public function transitions(WorkflowItem $item, Authenticatable $user = null)
    {
        return $item->getEntity()->states->filter(function (State $state) use($item, $user) {
            return $this->checkAccess($state->code, $item, $user);
        })->all();
    }

    public function transit(WorkflowItem $item, State $state, Authenticatable $user = null)
    {
        if (!$this->checkAccess($state->code, $item, $user)) {
            throw new AuthorizationException('В доступе отказано', 403);
        }

        $item->setStateId($state->id);
        $item->save();
    }

    /**
     * @param $action
     * @param WorkflowItem $item
     * @param Authenticatable|RolesOwner|null $user
     * @return bool
     */
    protected function checkAccess($action, WorkflowItem $item, Authenticatable $user = null)
    {
        $state = $this->itemState($item);
        $state_key = $state === null ? null : $state->id;
        $relations_keys = $this->itemRelations($item, $user)->keys()->all();
        $roles_keys = $this->userRoles($user)->keys()->all();
        $features_keys = $this->itemFeatures($item)->keys()->all();

        foreach ($this->permissions->permissionsFor($action, $item->getEntity()) as $permission) {
            if ($permission['authenticated'] && $user === null) {
                continue;
            }

            if (isset($permission['states']) && !in_array($state_key, $permission['states'])) {
                continue;
            }

            if (isset($permission['relations']) && !array_intersect($relations_keys, $permission['relations'])) {
                continue;
            }

            if (isset($permission['roles']) && !array_intersect($roles_keys, $permission['roles'])) {
                continue;
            }

            if (isset($permission['features']) && !array_intersect($features_keys, $permission['features'])) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $action
     * @param EloquentBuilder $query
     * @param Authenticatable|null $user
     * @return Builder|\Illuminate\Database\Eloquent\Builder
     */
    protected function filterQuery($action, EloquentBuilder $query, Authenticatable $user = null)
    {
        $query->where(function (EloquentBuilder $subQuery) use ($action, $user) {
            $model = $subQuery->getModel();
            /* @var WorkflowItem|Model $model */
            $roles_keys = $this->userRoles($user);
            $entity = $model->getEntity();

            $noPermission = true;

            foreach ($this->permissions->permissionsFor($action, $entity) as $permission) {
                if ($permission['authenticated'] && $user === null) {
                    continue;
                }

                if (isset($permission['roles']) && $roles_keys->keys()->intersect($permission['roles'])->isEmpty()) {
                    continue;
                }

                $noPermission = false;

                $subQuery->orWhere(function(EloquentBuilder $orBuilder) use ($entity, $model, $user, $permission) {
                    $unconditional = true;

                    if (isset($permission['states'])) {
                        $orBuilder->whereIn('state_id', $permission['states']);
                        $unconditional = false;
                    }

                    if (isset($permission['relations'])) {
                        $orBuilder->where(function(EloquentBuilder $builder) use ($permission, $entity, $model, $user) {
                            foreach ($entity->relationships as $relation) {
                                if (!in_array($relation->id, $permission['relations'])) {
                                    continue;
                                }
                                $builder->orWhere(function(EloquentBuilder $subBuilder) use($model, $relation, $user) {
                                    $query = $model::inRelation($relation->code, $user)->getQuery();
                                    $subBuilder->mergeWheres($query->wheres, $query->getBindings());
                                });
                            }
                        });
                        $unconditional = false;
                    }

                    if (isset($permission['features'])) {
                        $orBuilder->where(function(EloquentBuilder $builder) use ($permission, $entity, $model) {
                            foreach ($entity->features as $feature) {
                                if (!in_array($feature->id, $permission['features'])) {
                                    continue;
                                }
                                $builder->orWhere(function(EloquentBuilder $subBuilder) use($model, $feature) {
                                    $query = $model::havingFeature($feature->code)->getQuery();
                                    $subBuilder->mergeWheres($query->wheres, $query->getBindings());
                                });
                            }
                        });
                        $unconditional = false;
                    }

                    if ($unconditional) {
                        $orBuilder->whereRaw('1=1');
                    }
                });
            }

            if ($noPermission) {
                $subQuery->whereRaw('FALSE');
            }
        });

        return $query;
    }

    /**
     * @param WorkflowItem $item
     * @return State|null
     */
    protected function itemState(WorkflowItem $item)
    {
        return $item->getEntity()->states->first(function(State $state) use ($item) {
            return $state->id == $item->getStateId();
        });
    }

    /**
     * @param WorkflowItem $item
     * @param Authenticatable|null $user
     * @return Relation[]|Collection
     */
    protected function itemRelations(WorkflowItem $item, Authenticatable $user = null)
    {
        if ($user === null) {
            return collect([]);
        }

        return $item->getEntity()->relationships->filter(function(Relation $relation) use ($item, $user) {
            return $item->isUserInRelation($relation->code, $user);
        })->keyBy('id');
    }

    /**
     * @param $user
     * @return Role[]|Collection
     */
    protected function userRoles(RolesOwner $user = null)
    {
        return collect($user === null ? [] : $user->getRoles())->keyBy('id');
    }

    /**
     * @param WorkflowItem $item
     * @return Feature[]|Collection
     */
    protected function itemFeatures(WorkflowItem $item)
    {
        return $item->getEntity()->features->filter(function(Feature $feature) use($item) {
            return $item->hasFeature($feature->code);
        })->keyBy('id');
    }
}
