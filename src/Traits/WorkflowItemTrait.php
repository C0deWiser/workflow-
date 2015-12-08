<?php

namespace Media101\Workflow\Traits;
use Illuminate\Database\Eloquent\Model;

/**
 * @package Media101\Workflow\Traits
 */
class WorkflowItemTrait
{
    public static function workflowName()
    {
        return end(explode('\\', static::class));
    }

    public function workflowStates()
    {
        /* @var $this Model */
        return $this->hasOne(WorkflowState::class, 'state_id');
    }
}
