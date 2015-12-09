<?php

namespace Media101\Workflow\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Media101\Workflow\Contracts\Workflow
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
        return 'Media101\Workflow\Workflow';
    }
}
