<?php

namespace Codewiser\Workflow\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Codewiser\Workflow\Contracts\PermissionsStorage;
use Codewiser\Workflow\Models\State;

/**
 * Trait intended for classes which will be subjects to the workflow.
 * Only fits for Eloquent models.
 *
 * @property number $state_id
 * @property State $state
 *
 * @package Codewiser\Workflow\Traits
 */
trait WorkflowItem
{
    use AbstractWorkflowItem;

    public static function workflowName()
    {
        $chunks = explode('\\', static::class);
        return end($chunks);
    }

    public static function workflowActions()
    {
        return config('workflow.defaults.actions');
    }

    public function getStateId()
    {
        return $this->state_id;
    }

    public function setStateId($stateId)
    {
        // Походу, нет такого метода...
        unset($this->relations['state']);
        $this->state()->associate($stateId);

        return $this;
    }

    public function state()
    {
        /* @var $this Model */
        return $this->belongsTo(State::class, 'state_id');
    }

    // More functions

    /**
     * Modifies query so that only item in certain relation with the specified user are left.
     *
     * Defaults to calling methods like `scopeAsOwner`, but you can instead override this method.
     *
     * @param Builder $builder
     * @param string $relation
     * @param Authenticatable|null $user
     * @return Builder
     */
    public function scopeInRelation(Builder $builder, $relation, Authenticatable $user = null)
    {
        if ($user === null) {
            return $builder->whereRaw('FALSE');
        }

        $method = 'scopeAs' . app(Str::class)->studly($relation);
        return $this->$method($builder, $user);
    }

    /**
     * Modifies query so that only items having specified feature are left.
     *
     * Default to calling methods like `scopeBeingOpen` or `scopeBeingActive`.
     *
     * @param Builder $builder
     * @param string $feature
     * @return Builder
     */
    public function scopeHavingFeature(Builder $builder, $feature)
    {
        $method = 'scopeBeing' . app(Str::class)->studly($feature);
        return $this->$method($builder);
    }
}
