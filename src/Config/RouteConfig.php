<?php

namespace OmegaCode\JwtSecuredApiCore\Config;

use OmegaCode\JwtSecuredApiCore\Action\AbstractAction;

class RouteConfig
{
    private AbstractAction $action;

    private string $route;

    private array $allowedMethods;

    private array $middlewares;

    public function __construct(AbstractAction $action, string $route, array $allowedMethods, array $middlewares)
    {
        $this->action = $action;
        $this->route = $route;
        $this->allowedMethods = $allowedMethods;
        $this->middlewares = $middlewares;
    }

    public function getAction(): AbstractAction
    {
        return $this->action;
    }

    public function setAction(AbstractAction $action): void
    {
        $this->action = $action;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function setRoute(string $route): void
    {
        $this->route = $route;
    }

    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }

    public function setAllowedMethods(array $allowedMethods): void
    {
        $this->allowedMethods = $allowedMethods;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function setMiddlewares(array $middlewares): void
    {
        $this->middlewares = $middlewares;
    }
}
