<?php

namespace Codewiser\Workflow\Http\Middleware;

use Illuminate\Http\Request;

/**
 * Middleware to be used to protect whole resource. Requires two arguments:
 * * route parameter name
 * * class name
 */
class WorkflowMiddleware
{
    /**
     * @var Request
     */
    private $request;

    private $routeParameter;

    private $class;

    private $action;

    /**
     * @param Request $request
     * @param \Closure $next
     * @param string $routeParameter
     * @param string $class
     * @return mixed
     * @throws ThisJustCannotHappenException
     */
    public function handle(Request $request, \Closure $next, $routeParameter, $class)
    {
        $this->request = $request;
        $this->routeParameter = $routeParameter;
        $this->class = $class;
        if ($this->shouldCheck() && !\Gate::allows($this->getAbility(), $this->getInstance())) {
            abort(403, 'В доступе отказано');
        }
        return $next($request);
    }

    private function shouldCheck()
    {
        return in_array($this->getAction(), ['index', 'show', 'create', 'store', 'edit', 'update', 'destroy']);
    }

    private function getAction()
    {
        if ($this->action) {
            return $this->action;
        }

        return $this->action = explode('@', $this->request->route()->getActionName())[1];
    }

    private function getAbility()
    {
        switch ($this->getAction()) {
            case 'index':
                return 'list';
            case 'show':
                return 'view';
            case 'create':
            case 'store':
                return 'add';
            case 'edit':
            case 'update':
                return 'edit';
            case 'destroy':
                return 'delete';
            default:
                throw new ThisJustCannotHappenException();
        }
    }

    private function getInstance()
    {
        switch ($this->getAction()) {
            case 'index':
            case 'create':
            case 'store':
                return new $this->class();
            default:
                return $this->request->route($this->routeParameter);
        }
    }
}
