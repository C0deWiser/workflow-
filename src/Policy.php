<?php

namespace Media101\Workflow;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Query\Builder;
use Media101\Workflow\Contracts\WorkflowItem;
use Media101\Workflow\Models\Entity;
use Media101\Workflow\Models\Feature;
use Media101\Workflow\Models\Relation;
use Media101\Workflow\Models\Role;
use Media101\Workflow\Models\State;

/**
 * Base workflow module policy, allowing to check access even for anonymous users.
 *
 * @package Media101\Workflow
 */
class Policy
{
    /**
     * @var Preloader
     */
    protected $preloader;

    public function __construct(Preloader $preloader)
    {
        $this->preloader = $preloader;
    }

    public function __call($name, $arguments = [])
    {
        $user = $arguments[0] === 'guest' ? null : $arguments[0];
        $item = $arguments[1];
        return $this->checkAccess($name, $item, $user);
    }

    /**
     * @param $action
     * @param WorkflowItem $item
     * @param Authenticatable|null $user
     * @return bool
     */
    protected function checkAccess($action, WorkflowItem $item, $user)
    {
        $entity = $this->itemEntity($item);
        $state = $this->itemState($item);
        $relations = $this->itemRelations($item, $user);
        $roles = $this->userRoles($user);
        $features = $this->itemFeatures($item);

        $query = \DB::table(config('workflow.database.permissions_table'))
            ->select(['hasPermission' => 'COUNT(*) > 0'])
            ->where([
                'entity_id' => $entity->id,
                'action_id' => $entity->actions->keyBy('code')[$action],
            ])->where(function (Builder $query) use ($state) {
                $query->where('state_id', '=', $state->id)->orWhere('state_id IS NULL');
            });

        foreach (['relation', 'role', 'feature'] as $criterion) {
            $values = ${"{$criterion}s"};
            $query->where(function (Builder $query) use($criterion, $values) {
                $query->where($criterion, 'IN', collect($values)->keyBy('id')->keys())->orWhere("$criterion IS NULL");
            });
        }

        return !! $query->first()->hasPermission;
    }

    /**
     * @param WorkflowItem $item
     * @return Entity
     */
    protected function itemEntity(WorkflowItem $item)
    {
        return $this->preloader->entity($item->workflowName());
    }

    /**
     * @param WorkflowItem $item
     * @return State
     */
    protected function itemState(WorkflowItem $item)
    {
        return $item->state;
    }

    /**
     * @param WorkflowItem $item
     * @param $user
     * @return Relation[]
     */
    protected function itemRelations(WorkflowItem $item, $user)
    {
        // todo
        return [];
    }

    /**
     * @param $user
     * @return Role[]
     */
    protected function userRoles($user)
    {
        // todo
        return [];
    }

    /**
     * @param WorkflowItem $item
     * @return Feature[]
     */
    protected function itemFeatures(WorkflowItem $item)
    {
        // todo
        return [];
    }
}
