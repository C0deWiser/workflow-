<?php

namespace Media101\Workflow;

use Illuminate\Auth\Access\Gate;
use Media101\Workflow\Contracts\Workflow as WorkflowContract;

class Workflow extends Gate implements WorkflowContract
{
    public $defaultPolicy = Policy::class;

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
}
