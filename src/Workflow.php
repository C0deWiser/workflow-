<?php

namespace Media101\Workflow;

use Illuminate\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Builder;
use Media101\Workflow\Contracts\Workflow as WorkflowContract;
use Media101\Workflow\Contracts\WorkflowItem;

class Workflow extends Gate implements WorkflowContract
{
    public $defaultPolicy = Policy::class;

    public $defaultQueryPolicy = Policy::class;

    /**
     * @inheritdoc
     *
     * Workflow items always correspond to a policy.
     */
    protected function firstArgumentCorrespondsToPolicy(array $arguments)
    {
        if (isset($arguments[0]) && is_object($arguments[0]) &&
                class_implements($arguments[0], WorkflowItem::class)) {
            return true;
        }

        return parent::firstArgumentCorresponsedToPolicy($arguments);
    }

    /**
     * @inheritdoc
     *
     * Overriden to always apply default policy (for WorkflowItem).
     */
    public function getPolicyFor($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (isset($this->policies[$class])) {
            $policy = $this->policies[$class];
        } elseif (class_implements($class, WorkflowItem::class)) {
            $policy = $this->defaultPolicy;
        } else {
            throw new \InvalidArgumentException("Policy not defined for [{$class}].");
        }

        return $this->resolvePolicy($policy);
    }

    /**
     * @inheritdoc
     */
    public function filter($action, Builder $queryBuilder)
    {
        $policy = $this->resolvePolicy($this->defaultQueryPolicy);
        $user = $this->resolveUser();
        return $policy->filter($action, $queryBuilder, is_object($user) ? $user : null);
    }

    /**
     * Filters only those records user is NOT allowed to apply specified action to.
     *
     * @param string $action
     * @param Builder $queryBuilder
     * @return Builder
     */
    public function except($action, Builder $queryBuilder)
    {
        $policy = $this->resolvePolicy($this->defaultQueryPolicy);
        $user = $this->resolveUser();
        return $policy->except($action, $queryBuilder, is_object($user) ? $user : null);
    }
}
