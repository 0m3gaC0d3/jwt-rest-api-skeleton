<?php

declare(strict_types=1);

namespace App\Manager;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Service\ConfigurationFileService;
use App\Service\DatabaseService;
use Slim\App as API;

class RouteManager
{
    private const ALLOWED_METHODS = [
        "get",
        "post",
        "put",
        "delete",
        "patch",
    ];

    private const REQUIRED_PROPERTIES = ['controller', 'route', 'method', 'action'];

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
            $class = trim($routeConfiguration['controller']);
            $route = trim($routeConfiguration['route']);
            $method = trim($routeConfiguration['method']);
            $action = trim($routeConfiguration['action']);
            $controller = new $class;
            $this->handleRequest($method, $route, $controller, $action);
        }
    }

    protected function validateRouteConfiguration(array $routeConfiguration)
    {
        if (array_diff_key(array_flip(self::REQUIRED_PROPERTIES), $routeConfiguration)) {
            throw new \RuntimeException("You have an error in your routes.yaml file");
        }
        if (!class_exists(trim($routeConfiguration['controller']))) {
            throw new \InvalidArgumentException("The class ".$routeConfiguration['class']." does not exist");
        }
        if (empty(trim($routeConfiguration['route']))) {
            throw new \InvalidArgumentException("The property route can not be empty");
        }
        if (empty(trim($routeConfiguration['method']))) {
            throw new \InvalidArgumentException("The property method can not be empty");
        }
        if (empty(trim($routeConfiguration['action']))) {
            throw new \InvalidArgumentException("The property action can not be empty");
        }
    }

    private function handleRequest(string $method, string $route, object $controller, string $action)
    {
        if (!in_array($method, self::ALLOWED_METHODS)) {
            throw new \InvalidArgumentException(
                "The method $method is not allowed. Allowed methods are: ".implode(', ', self::ALLOWED_METHODS)
            );
        }
        $action = $action."Action";
        $this->api->$method(
            $route,
            function (Request $request, Response $response, array $args) use ($controller, $action) {
                return $controller->$action($this, $request, $response, $args);
            }
        );
    }
}