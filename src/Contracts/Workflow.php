<?php

namespace Media101\Workflow\Contracts;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Builder;

/**
 * Special gate with default user resolver returning "guest" string for not-authenticated user,
 * so that the policy have a chance to allow the guest to perform actions.
 *
 * Also provides the functionality to filter the queries (leaving only allowed records).
 *
 * Besides, with no policies and abilities defined default workflow permissions will be checked.
 *
 * @package Media101\Workflow\Contracts
 */
interface Workflow extends Gate
{
    /**
     * @param string $action
     * @param Builder $queryBuilder
     * @return Builder
     */
    public function filter($action, Builder $queryBuilder);
}
