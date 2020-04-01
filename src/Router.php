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

namespace OmegaCode\JwtSecuredApiCore;

use Exception;
use InvalidArgumentException;
use OmegaCode\JwtSecuredApiCore\Action\AbstractAction;
use OmegaCode\JwtSecuredApiCore\Auth\JsonWebTokenAuth;
use OmegaCode\JwtSecuredApiCore\Middleware\JsonWebTokenMiddleware;
use OmegaCode\JwtSecuredApiCore\Service\ConfigurationFileService;
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

    private JsonWebTokenAuth $auth;

    public function __construct(
        API $api,
        ConfigurationFileService $configurationFileService,
        JsonWebTokenAuth $auth
    ) {
        $this->api = $api;
        $this->configurationFileService = $configurationFileService;
        $this->auth = $auth;
    }

    public function registerRoutes(ContainerInterface $container): void
    {
        $routesConfiguration = $this->configurationFileService->load('routes.yaml')['routes'];
        // TODO validate routes con figuration and error on duplicate entries.
        foreach ($routesConfiguration as $group) {
            foreach ($group as $name => $configuration) {
                $actionClass = trim((string)$configuration['action']);
                $route = trim((string)$configuration['route']);
                $method = strtolower(trim((string)$configuration['method']));
                $protected = (bool) $configuration['protected'];
                if (!$container->has($actionClass)) {
                    throw new Exception("Could not find controller service with id: $actionClass");
                }
                $action = $container->get($actionClass);
                $this->handleRequest($method, $route, $action, $protected);
            }
        }
    }

    private function handleRequest(string $method, string $route, AbstractAction $action, bool $protected): void
    {
        if (!in_array($method, self::ALLOWED_METHODS)) {
            throw new InvalidArgumentException("Method $method is not allowed");
        }
        /** @var RouteInterface $router */
        $router = $this->api->$method(
            $route,
            function (Request $request, Response $response) use ($action) {
                return $action($request, $response);
            }
        );
        if ($protected) {
            $router->addMiddleware(new JsonWebTokenMiddleware($this->auth, $this->api->getResponseFactory()));
        }
    }
}
