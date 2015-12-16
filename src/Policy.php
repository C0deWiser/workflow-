<?php

namespace Media101\Workflow;

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
        $criteria = $this->filterQuery($action, $queryBuilder->getModel(), $user);
        $queryBuilder->getQuery()->addNestedWhereQuery($criteria);
        return $queryBuilder;
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
     * @param WorkflowItem|Model $model
     * @param Authenticatable|null $user
     * @return Builder
     */
    protected function filterQuery($action, WorkflowItem $model, Authenticatable $user = null)
    {
        $queryBuilder = $model->newQueryWithoutScopes()->getQuery();
        $roles_keys = $this->userRoles($user);
        $entity = $model->getEntity();

        $noPermission = true;
        foreach ($this->permissions->permissionsFor($action, $entity) as $permission) {
            if (isset($permission['roles']) && !array_intersect($roles_keys, $permission['roles'])) {
                continue;
            }

            $noPermission = false;

            $queryBuilder->orWhere(function(Builder $orBuilder) use($entity, $model, $user) {
                if (isset($permission['states'])) {
                    $orBuilder->where([ 'state_id' => $permission['states'] ]);
                }

                if (isset($permission['relations'])) {
                    $orBuilder->where(function(Builder $builder) use ($permission, $entity, $model, $user) {
                        foreach ($entity->relations as $relation) {
                            if (!in_array($relation->id, $permission['relations'])) {
                                continue;
                            }
                            $builder->orWhere(function(Builder $subBuilder) use($model, $relation, $user) {
                                return $model->onlyInRelation($subBuilder, $relation->code, $user);
                            });
                        }
                    });
                }

                if (isset($permission['features'])) {
                    $orBuilder->where(function(Builder $builder) use ($permission, $entity, $model) {
                        foreach ($entity->features as $feature) {
                            if (!in_array($feature->id, $permission['features'])) {
                                continue;
                            }
                            $builder->orWhere(function(Builder $subBuilder) use($model, $feature) {
                                return $model->onlyHavingFeature($subBuilder, $feature->code);
                            });
                        }
                    });
                }
            });
        }

        if ($noPermission) {
            $queryBuilder->where('FALSE');
        }

        return $queryBuilder;
    }

    /**
     * @param WorkflowItem $item
     * @return State|null
     */
    protected function itemState(WorkflowItem $item)
    {
        return $item->getEntity()->states->first(function($key, State $state) use ($item) {
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

        $item->getEntity()->relations->filter(function(Relation $relation) use ($item, $user) {
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
