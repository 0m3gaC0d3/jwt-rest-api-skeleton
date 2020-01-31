<?php

declare(strict_types=1);

namespace PSVneo\Request;

use PSVneo\Service\ConfigurationFileService;
use Slim\App as API;

class RouteManager
{
    private API $api;

    private ConfigurationFileService $configurationFileService;

    public function __construct(API $api, ConfigurationFileService $configurationFileService)
    {
        $this->api = $api;
        $this->configurationFileService = $configurationFileService;
    }

    public function registerRoutes()
    {
        $routesConfiguration = $this->configurationFileService->load('routes.yaml')['routes'];
        foreach ($routesConfiguration as $routeConfiguration) {
            $this->validateRouteConfiguration($routeConfiguration);
            $class = (trim($routeConfiguration['class']));
            $apiRequest = new $class;
            if (!$apiRequest instanceof ApiRequestInterface) {
                throw new \Exception(
                    "Class ".get_class($apiRequest)." must implement the interface ".ApiRequestInterface::class
                );
            }
            $this->registerRoute($apiRequest, trim($routeConfiguration['route']));
        }
    }

    protected function validateRouteConfiguration(array $routeConfiguration)
    {
        if (!isset($routeConfiguration['class'], $routeConfiguration['route'])) {
            throw new \RuntimeException("You have an error in your routes.yaml file");
        }
        if (!class_exists(trim($routeConfiguration['class']))) {
            throw new \InvalidArgumentException("The class ".$routeConfiguration['class']." does not exist");
        }
        if (empty(trim($routeConfiguration['route']))) {
            throw new \InvalidArgumentException("The property route can not be empty");
        }
    }

    protected function registerRoute(ApiRequestInterface $apiRequest, string $route)
    {
        $apiRequest->handleRequest($this->api, $route);
    }
}