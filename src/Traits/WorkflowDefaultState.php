<?php

namespace Media101\Workflow\Traits;

use Illuminate\Database\Eloquent\Model;
use Media101\Workflow\Contracts\WorkflowItem as WorkflowItemContract;

/**
 * The trait will enable class to set the initial workflow state. By default it will be the first
 * state returned in workflow states array, but you can override method `initState` to set to
 * any state you want.
 *
 * @package Media101\Workflow\Traits
 */
trait WorkflowDefaultState
{
    public function initState()
    {
        /* @var WorkflowItemContract $this */
        if (!$this->state) {
            $this->state_id = $this->getEntity()->states->first()->id;
        }
    }

    public static function bootWorkflowDefaultState()
    {
        $class = get_called_class();
        /* @var Model $class */
        $class::creating(function (Model $model) {
            /* @var WorkflowDefaultState $model */
            $model->initState();
        });
    }
}
