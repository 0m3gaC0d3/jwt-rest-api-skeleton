<?php

declare(strict_types=1);

namespace App\Manager;

use App\Controller\ControllerInterface;
use App\Controller\StandardController;
use Doctrine\Common\Annotations\AnnotationReader;
use OmegaCode\DebuggerUtility;
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


    public function __construct(
        API $api,
        ConfigurationFileService $configurationFileService

    ) {
        $this->api = $api;
        $this->configurationFileService = $configurationFileService;
    }

    public function registerRoutes()
    {

        $controllerConfiguration = $this->getControllerConfigurations();

        $routesConfiguration = $this->configurationFileService->load('routes.yaml')['routes'];
        foreach ($controllerConfiguration as $configuration) {
            $class = trim($configuration['controller']);
            $route = trim($configuration['route']);
            $method = trim($configuration['method']);
            $action = trim($configuration['action']);
            $controller = new $class;
            $this->handleRequest($method, $route, $controller, $action);
        }
    }

    private function handleRequest(string $method, string $route, object $controller, string $action)
    {
        if (!in_array($method, self::ALLOWED_METHODS)) {
            throw new \InvalidArgumentException(
                "The method $method is not allowed. Allowed methods are: ".implode(', ', self::ALLOWED_METHODS)
            );
        }
        $this->api->$method(
            $route,
            function (Request $request, Response $response, array $args) use ($controller, $action) {
                return $controller->$action($this, $request, $response, $args);
            }
        );
    }

    private function getControllerConfigurations()
    {
        // todo refactor me
        $configuration = [];
        $reader = new AnnotationReader();
        $controllerClasses = $this->configurationFileService->load('controllers.yaml')['controllers'];
        foreach ($controllerClasses as $controllerClass ) {
            $class = new \ReflectionClass($controllerClass);
            $methods = $class->getMethods();
            /** @var \ReflectionMethod $method */
            foreach ($methods as $method) {
                $methodAnnotations = $reader->getMethodAnnotations($method);
                foreach ($methodAnnotations as $annotation) {
                    if ($annotation instanceof \App\Annotation\ControllerAnnotation) {
                        if (isset($configuration[$class->getName().'::'.$method->getName()])) {
                            continue;
                        }
                        $configuration[$class->getName().'::'.$method->getName()] = [
                            "controller" => $class->getName(),
                            "method" => $annotation->method,
                            "route" => $annotation->route,
                            "action" => $method->getName()
                        ];
                    }
                }
            }
        }
        return $configuration;
    }
}
