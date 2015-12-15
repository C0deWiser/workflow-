<?php

namespace Media101\Workflow\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Media101\Workflow\Contracts\PermissionsStorage;
use Media101\Workflow\Models\State;

/**
 * Trait intended for classes which will be subjects to the workflow.
 *
 * @property number $state_id
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

    public function getEntity()
    {
        return app(PermissionsStorage::class)->entity(static::workflowName());
    }

    public function getStateId()
    {
        return $this->state_id;
    }

    public function state()
    {
        /* @var $this Model */
        return $this->belongsTo(State::class, 'state_id');
    }

    // More functions

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

    /**
     * Modifies query so that only item in certain relation with the specified user are left.
     *
     * Defaults to calling static methods like `onlyAsOwner`, but you can instead override this method.
     *
     * @param Builder $builder
     * @param string $relation
     * @param Authenticatable|null $user
     * @return Builder
     */
    public static function onlyInRelation($builder, $relation, Authenticatable $user = null)
    {
        if ($user === null) {
            return $builder->where('FALSE');
        }

        $method = 'onlyAs' . app(Str::class)->studly($relation);
        return static::$method($builder, $user);
    }

    /**
     * Modifies query so that only items having specified feature are left.
     *
     * Default to calling static methods like `onlyBeingOpen` or `onlyBeingActive`.
     *
     * @param Builder $builder
     * @param string $feature
     * @return Builder
     */
    public static function onlyHavingFeature($builder, $feature)
    {
        $method = 'onlyBeing' . app(Str::class)->studly($feature);
        return static::$method($builder);
    }
}
