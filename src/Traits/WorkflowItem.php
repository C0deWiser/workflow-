<?php

namespace Media101\Workflow\Traits;

use Illuminate\Database\Eloquent\Model;
use Media101\Workflow\Models\State;

/**
 * Trait intended for classes which will be subjects to the workflow.
 *
 * @property State $state
 *
 * @package Media101\Workflow\Traits
 */
trait WorkflowItem
{
    public static function workflowName()
    {
        $chunks = explode('\\', static::class);
        return end($chunks);
    }

    public static function workflowStates()
    {
        return [];
    }

    public static function workflowRelations()
    {
        return [];
    }

    public static function workflowFeatures()
    {
        return [];
    }

    public static function workflowActions()
    {
        return config('workflow.defaults.actions');
    }

    public function state()
    {
        /* @var $this Model */
        return $this->hasOne(State::class, 'state_id');
    }
}
