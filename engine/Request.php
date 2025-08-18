<?php

namespace Engine;

use Engine\Routing\Route;
use Closure;

/**
 * Request.php
 *
 * Class Request contains info about request.
 */
class Request
{

    /**
     * Request parameters.
     *
     * @var array
     */
    public array $parameters;

    /**
     * Route that will be executed.
     *
     * @var Route
     */
    public ?Route $route;

    /**
     * View that will be displayed.
     *
     * @var View
     */
    public ?View $view;

    /**
     * Post response job.
     *
     * @var Closure
     */
    public ?Closure $post_response;

    /**
     * Request constructor.
     *
     * @param array $parameters - To pass in controller
     * @param Route $route - Route to follow
     */
    public function __construct(array $parameters, Route $route = null)
    {
        $this->parameters = $parameters;
        $this->route = $route;
    }

}
