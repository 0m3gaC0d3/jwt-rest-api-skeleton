<?php

declare(strict_types=1);

namespace App\Manager;

use App\Middleware\JsonWebTokenMiddleware;
use App\Service\ControllerAnnotationService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Service\ConfigurationFileService;
use Slim\App as API;
use Slim\Interfaces\RouteInterface;

class RouteManager
{
    private const ALLOWED_METHODS = [
        "get",
        "post",
        "put",
        "delete",
        "patch",
    ];

    private API $api;

    private ConfigurationFileService $configurationFileService;

    private ContainerInterface $container;

    public function __construct(API $api, ConfigurationFileService $configurationFileService)
    {
        $this->api = $api;
        $this->configurationFileService = $configurationFileService;
        $this->container = $api->getContainer();
    }

    public function registerRoutes()
    {
        /** @var ControllerAnnotationService $controllerAnnotationService */
        $controllerAnnotationService = $this->container->get(ControllerAnnotationService::class);
        $controllerConfiguration = $controllerAnnotationService->getConfiguration();
        foreach ($controllerConfiguration as $configuration) {
            $class = trim($configuration['controller']);
            $route = trim($configuration['route']);
            $method = strtolower(trim($configuration['method']));
            $action = trim($configuration['action']);
            $protected = (bool)$configuration['protected'];
            $controller = new $class;
            $this->handleRequest($method, $route, $controller, $action, $protected);
        }
    }

    private function handleRequest(string $method, string $route, object $controller, string $action, bool $protected)
    {
        if (!in_array($method, self::ALLOWED_METHODS)) {
            throw new \InvalidArgumentException(
                "The method $method is not allowed. Allowed methods are: ".implode(', ', self::ALLOWED_METHODS)
            );
        }
        /** @var RouteInterface $router */
        $router = $this->api->$method(
            $route,
            function (Request $request, Response $response, array $args) use ($controller, $action) {
                return $controller->$action($this, $request, $response, $args);
            }
        );
        if ($protected) {
            $router->addMiddleware(new JsonWebTokenMiddleware(
                $this->api->getContainer()->get('api.auth.jwt'),
                $this->api->getResponseFactory()
            ));
        }
    }
}
