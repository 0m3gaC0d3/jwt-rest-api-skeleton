<?php

declare(strict_types=1);

namespace App;

use App\Auth\JsonWebTokenAuth;
use App\Middleware\JsonWebTokenMiddleware;
use App\Service\ControllerAnnotationService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Service\ConfigurationFileService;
use Slim\App as API;
use Slim\Interfaces\RouteInterface;
use InvalidArgumentException;

class Router
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

    private ControllerAnnotationService $controllerAnnotationService;

    private JsonWebTokenAuth $auth;

    public function __construct(
        API $api,
        ConfigurationFileService $configurationFileService,
        ControllerAnnotationService $controllerAnnotationService,
        JsonWebTokenAuth $auth
    ) {
        $this->api = $api;
        $this->configurationFileService = $configurationFileService;
        $this->controllerAnnotationService = $controllerAnnotationService;
        $this->auth = $auth;
    }

    public function registerRoutes(ContainerInterface $container): void
    {
        $controllerConfiguration = $this->controllerAnnotationService->getConfiguration();
        foreach ($controllerConfiguration as $configuration) {
            $class = trim($configuration['controller']);
            $route = trim($configuration['route']);
            $method = strtolower(trim($configuration['method']));
            $action = trim($configuration['action']);
            $protected = (bool)$configuration['protected'];
            if (!$container->has($class)) {
                throw new \Exception("Could not find controller service with id: $class");
            }
            $controller = $container->get($class);
            $this->handleRequest($method, $route, $controller, $action, $protected);
        }
    }

    private function handleRequest(
        string $method,
        string $route,
        object $controller,
        string $action,
        bool $protected
    ): void {
        if (!in_array($method, self::ALLOWED_METHODS)) {
            throw new InvalidArgumentException(
                "The method $method is not allowed. Allowed methods are: " . implode(', ', self::ALLOWED_METHODS)
            );
        }
        /** @var RouteInterface $router */
        $router = $this->api->$method(
            $route,
            function (Request $request, Response $response, array $args) use ($controller, $action) {
                if (!is_callable([$controller, $action])) {
                    throw new \Exception(
                        "Can not call ".get_class($controller).'::'.$action.'. Method must be public.'
                    );
                }

                return $controller->$action($request, $response, $args);
            }
        );
        if ($protected) {
            $router->addMiddleware(new JsonWebTokenMiddleware($this->auth, $this->api->getResponseFactory()));
        }
    }
}
