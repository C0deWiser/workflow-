<?php

namespace Media101\Workflow;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Query\Builder;
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
 * So, it any of such allowing records are in the permissions table - access will be granted, and denied otherwise.
 *
 * @package Media101\Workflow
 */
class Policy
{
    /**
     * @var PermissionsStorageContract
     */
    protected $permissions;

    public function __construct(PermissionsStorageContract $permissions)
    {
        $this->permissions = $permissions;
    }

    public function __call($name, $arguments = [])
    {
        $user = is_object($arguments[0]) ? $arguments[0] : null;
        $item = $arguments[1];
        return $this->checkAccess($name, $item, $user);
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
        $relations = $this->itemRelations($item, $user);
        $roles = $this->userRoles($user);
        $features = $this->itemFeatures($item);

        $query = \DB::table(config('workflow.database.permissions_table'))
            ->select(['hasPermission' => 'COUNT(*) > 0'])
            ->where([
                'entity_id' => $item->getEntity()->id,
                'action_id' => $item->getEntity()->actions->keyBy('code')[$action],
            ])->where(function(Builder $query) use ($state) {
                $query->where('state_id', '=', $state->id)->orWhere('state_id IS NULL');
            });

        foreach (['relation', 'role', 'feature'] as $criterion) {
            $values = ${"{$criterion}s"};
            $query->where(function(Builder $query) use($criterion, $values) {
                $query->where($criterion, 'IN', collect($values)->keyBy('id')->keys())->orWhere("$criterion IS NULL");
            });
        }

        return !! $query->first()->hasPermission;
    }

    /**
     * @param WorkflowItem $item
     * @return State
     */
    protected function itemState(WorkflowItem $item)
    {
        return array_first($item->getEntity()->states, function(State $state) use ($item) {
            return $state->id == $item->getStateId();
        });
    }

    /**
     * @param WorkflowItem $item
     * @param Authenticatable|null $user
     * @return Relation[]
     */
    protected function itemRelations(WorkflowItem $item, Authenticatable $user)
    {
        return array_filter($item->getEntity()->relations, function(Relation $relation) use ($item, $user) {
            return $item->isUserInRelation($relation->code, $user);
        });
    }

    /**
     * @param $user
     * @return Role[]
     */
    protected function userRoles(RolesOwner $user = null)
    {
        return $user->getRoles();
    }

    /**
     * @param WorkflowItem $item
     * @return Feature[]
     */
    protected function itemFeatures(WorkflowItem $item)
    {
        return array_filter($item->getEntity()->features, function(Feature $feature) use($item) {
            return $item->hasFeature($feature->code);
        });
    }
}
