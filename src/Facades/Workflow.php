<?php

namespace Codewiser\Workflow\Facades;

use Illuminate\Support\Facades\Facade;
use Codewiser\Workflow\Contracts\Workflow as WorkflowContract;

/**
 * @see \Codewiser\Workflow\Contracts\Workflow
 */
class Workflow extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return WorkflowContract::class;
    }
}
