<?php

/**
 * MIT License
 *
 * Copyright (c) 2020 Wolf Utz<wpu@hotmail.de>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace App;

use App\Auth\JsonWebTokenAuth;
use App\Middleware\JsonWebTokenMiddleware;
use App\Service\ConfigurationFileService;
use App\Service\ControllerAnnotationService;
use Exception;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App as API;
use Slim\Interfaces\RouteInterface;

class Router
{
    private const ALLOWED_METHODS = [
        'get',
        'post',
        'put',
        'delete',
        'patch',
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
            $protected = (bool) $configuration['protected'];
            if (!$container->has($class)) {
                throw new Exception("Could not find controller service with id: $class");
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
            throw new InvalidArgumentException("The method $method is not allowed. Allowed methods are: " . implode(', ', self::ALLOWED_METHODS));
        }
        /** @var RouteInterface $router */
        $router = $this->api->$method(
            $route,
            function (Request $request, Response $response, array $args) use ($controller, $action) {
                if (!is_callable([$controller, $action])) {
                    throw new Exception('Can not call ' . get_class($controller) . '::' . $action . '. Method must be public.');
                }

                return $controller->$action($request, $response, $args);
            }
        );
        if ($protected) {
            $router->addMiddleware(new JsonWebTokenMiddleware($this->auth, $this->api->getResponseFactory()));
        }
    }
}
