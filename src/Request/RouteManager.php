<?php

declare(strict_types=1);

namespace PSVneo\Request;

use PSVneo\Request\Standard\StandardRequest;
use Slim\App as API;

class RouteManager
{
    private API $api;

    public function __construct(API $api)
    {
        $this->api = $api;
    }

    public function registerRoutes()
    {
        $this->registerRoute(new StandardRequest(), "/");
    }

    protected function registerRoute(ApiRequestInterface $apiRequest, string $route)
    {
        $apiRequest->handleRequest($this->api, $route);
    }
}