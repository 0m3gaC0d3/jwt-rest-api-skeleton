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
use Kint\Kint;
use OmegaCode\JwtSecuredApiCore\Action\AbstractAction;
use OmegaCode\JwtSecuredApiCore\Auth\JsonWebTokenAuth;
use OmegaCode\JwtSecuredApiCore\Config\RouteConfig;
use OmegaCode\JwtSecuredApiCore\Factory\RouteConfigFactory;
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
        // TODO validate routes configuration and error on duplicate entries.
        foreach ($routesConfiguration as $group) {
            foreach ($group as $name => $configuration) {
                $routeConfig = RouteConfigFactory::build($container, $configuration);
                $this->handleRequest($routeConfig);
            }
        }
    }

    private function handleRequest(RouteConfig $config): void
    {
        if ($config->getAllowedMethods() != array_intersect($config->getAllowedMethods(), self::ALLOWED_METHODS)) {
            throw new InvalidArgumentException("One or more of the given route ".$config->getRoute()." is not allowed");
        }
        /** @var string $method */
        foreach ($config->getAllowedMethods() as $method) {
            $action = $config->getAction();
            /** @var RouteInterface $router */
            $router = $this->api->$method(
                $config->getRoute(),
                function (Request $request, Response $response) use ($action) {
                    return $action($request, $response);
                }
            );
            $this->addMiddlewares($router, $config->getMiddlewares());
        }
    }

    private function addMiddlewares(RouteInterface $router, array $middlewares) : void
    {
        if (0 === count($middlewares)) {
            return;
        }
        /** @var string $middleware */
        foreach ($middlewares as $middleware) {
            $router->add($middleware);
        }
    }
}
