<?php

namespace Media101\Workflow\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Media101\Workflow\Contracts\PermissionsStorage;

/**
 * Trait intended for classes which will be subjects to the workflow.
 * Fits for items not representing some concrete objects, but rather abstract notions.
 *
 * @package Media101\Workflow\Traits
 */
trait AbstractWorkflowItem
{
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

    public function getEntity()
    {
        return app(PermissionsStorage::class)->entity(static::workflowName());
    }

    public function getStateId()
    {
        return null;
    }

    public function state()
    {
        return null;
    }

    public function isInRelation($relation)
    {
        return false;
    }

    public function isUserInRelation($relation, Authenticatable $user = null)
    {
        return false;
    }

    public function hasFeature($feature)
    {
        return false;
    }

    public function scopeInRelation(EloquentBuilder $query, $relation, Authenticatable $user = null)
    {
        throw new \Exception('impossible');
    }

    public function scopeHavingFeature(EloquentBuilder $query, $feature)
    {
        throw new \Exception('impossible');
    }
}
