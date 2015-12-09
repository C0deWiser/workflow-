<?php

namespace Media101\Workflow;

use Illuminate\Auth\Access\Gate;

/**
 * Special gate with default user resolver returning "guest" string for not-authenticated user,
 * so that the policy have a chance to allow the guest to perform actions.
 *
 * Besides, with no policies and abilities defined default workflow permissions will be checked.
 *
 * @package Media101\Workflow\Contracts
 */
class Workflow extends Gate
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
