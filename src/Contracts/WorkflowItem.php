<?php

namespace Media101\Workflow\Contracts;

use Media101\Workflow\Models\State;

/**
 * Contract to be implemented by all the class participating in the workflow.
 *
 * @property State $state
 *
 * @package Media101\Workflow\Contracts
 */
interface WorkflowItem
{
    public static function workflowName();

    public static function workflowStates();

    public static function workflowRelations();

    public static function workflowFeatures();

    public static function workflowActions();

    public function state();

}
