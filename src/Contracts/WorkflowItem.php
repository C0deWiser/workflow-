<?php

namespace Codewiser\Workflow\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Codewiser\Workflow\Models\Entity;
use Codewiser\Workflow\Models\State;

/**
 * Contract to be implemented by all the class participating in the workflow.
 *
 * @property State $state
 *
 * @package Codewiser\Workflow\Contracts
 */
interface WorkflowItem
{
    /**
     * Workflow entity name
     *
     * @return string
     */
    public static function workflowName();

    /**
     * All the states (codes) this entity instances can have
     *
     * @return string[]
     */
    public static function workflowStates();

    /**
     * Return array of all codes of the relations this item can have with the users.
     * Relations should (but do not have to be) in snake_case
     *
     * @return string[]
     */
    public static function workflowRelations();

    /**
     * Return array (codes) of all features instances of this workflow entity can possess
     *
     * @return string[]
     */
    public static function workflowFeatures();

    /**
     * Return array of all actions (codes) that can be applied to this entity
     *
     * @return string[]
     */
    public static function workflowActions();

    /**
     * Entity representing this instance in the workflow
     *
     * @return Entity
     */
    public function getEntity();

    /**
     * Return state identifier (without loading the state from the database, if possible)
     *
     * @return number
     */
    public function getStateId();

    /**
     * Set state id without checking.
     *
     * @param number $stateId
     */
    public function setStateId($stateId);

    /**
     * Return state of this item (as an object)
     *
     * @return State
     */
    public function state();

    /**
     * Is the current user in specified relationship with this item
     *
     * @param string $relation
     * @return bool
     */
    public function isInRelation($relation);

    /**
     * Is the specified user in specified relationship with this item
     *
     * @param string $relation
     * @param Authenticatable|null $user
     * @return bool
     */
    public function isUserInRelation($relation, Authenticatable $user = null);

    /**
     * Does this item possess certain feature
     *
     * @param string $feature
     * @return bool
     */
    public function hasFeature($feature);

    /**
     * Scope leaving only those records having a particular relation to the user.
     *
     * @param EloquentBuilder $query
     * @param string $relation
     * @param Authenticatable|null $user
     * @return EloquentBuilder
     */
    public function scopeInRelation(EloquentBuilder $query, $relation, Authenticatable $user = null);

    /**
     * Scope containing records having certain feature.
     *
     * @param EloquentBuilder $query
     * @param string $feature
     * @return EloquentBuilder
     */
    public function scopeHavingFeature(EloquentBuilder $query, $feature);
}
