<?php

namespace Media101\Workflow\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Str;
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

    /**
     * Check if user is in particular relation with item. Not recommended to override
     *
     * @param string $relation
     * @return bool
     */
    public function isInRelation($relation)
    {
        return $this->isUserInRelation($relation, app(Guard::class)->user());
    }

    /**
     * Check if some specified user is in certain relation with item.
     * Defaults to calling methods like `isUserOwner` on this item. You have a choice - either implement those method,
     * or override this very method.
     *
     * @param string $relation
     * @param Authenticatable|null $user
     * @return bool
     */
    public function isUserInRelation($relation, Authenticatable $user = null)
    {
        if ($user === null) {
            return false;
        }

        return $this->{'isUser' . app(Str::class)->studly($relation)}($user);
    }

    /**
     * Check if the item possess certain feature. Default to calling methods like `isOpen` or `isActive` on this item.
     * You have a choice either to implement those methods or override this method.
     *
     * @param string $feature
     * @return bool
     */
    public function hasFeature($feature)
    {
        return $this->{'is' . app(Str::class)->studly($feature)}();
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
