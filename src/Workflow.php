<?php

namespace Media101\Workflow;

use Illuminate\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Builder;
use Media101\Workflow\Contracts\Workflow as WorkflowContract;

class Workflow extends Gate implements WorkflowContract
{
    public $defaultPolicy = Policy::class;

    public $defaultQueryPolicy = Policy::class;

    /**
     * @inheritdoc
     */
    protected function resolveAuthCallback($user, $ability, array $arguments)
    {
        if ($this->firstArgumentCorrespondsToPolicy($arguments) || !isset($this->abilities[$ability])) {
            return $this->resolvePolicyCallback($user, $ability, $arguments);
        } else {
            return $this->abilities[$ability];
        }
    }

    /**
     * @inheritdoc
     */
    public function getPolicyFor($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (isset($this->policies[$class])) {
            $policy = $this->policies[$class];
        } else {
            $policy = $this->defaultPolicy;
        }

        return $this->resolvePolicy($policy);
    }

    /**
     * @inheritdoc
     */
    public function filter($action, Builder $queryBuilder)
    {
        $policy = $this->resolvePolicy($this->defaultQueryPolicy);
        return $policy->filter($action, $queryBuilder, app(Guard::class)->user());
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
        return $policy->except($action, $queryBuilder);
    }
}
