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

namespace OmegaCode\JwtSecuredApiCore\Core;

use OmegaCode\JwtSecuredApiCore\Event\Request\PostRequestEvent;
use OmegaCode\JwtSecuredApiCore\Event\Request\PreRequestEvent;
use OmegaCode\JwtSecuredApiCore\Generator\RequestIDGenerator;
use OmegaCode\JwtSecuredApiCore\Route\Configuration;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteInterface;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Api
{
    protected App $slimApp;

    protected EventDispatcher $eventDispatcher;

    protected AbstractAdapter $cache;

    public function __construct(App $slimApp, EventDispatcher $eventDispatcher, AbstractAdapter $cache)
    {
        $this->slimApp = $slimApp;
        $this->eventDispatcher = $eventDispatcher;
        $this->cache = $cache;
    }

    public function addRoute(string $method, Configuration $config): void
    {
        $action = $config->getAction();
        $eventDispatcher = $this->eventDispatcher;
        $cache = $this->cache;
        $cacheEnabled = (bool) $_ENV['ENABLE_REQUEST_CACHE'] && $config->isCacheable();
        /** @var RouteInterface $router */
        $router = $this->slimApp->$method(
            $config->getRoute(),
            function (Request $request, Response $response) use ($action, $eventDispatcher, $cache, $cacheEnabled) {
                $eventDispatcher->dispatch(new PreRequestEvent($request, $response), PreRequestEvent::NAME);
                $response = $action($request, $response);
                $eventDispatcher->dispatch(new PostRequestEvent($request, $response), PostRequestEvent::NAME);
                $identifier = RequestIDGenerator::generate($request);
                if ($cacheEnabled && $response->getStatusCode() === 200) {
                    $item = $cache->getItem($identifier);
                    $item->expiresAfter((int) $_ENV['REQUEST_CACHE_LIFE_TIME']);
                    $item->set((string) $response->getBody());
                    $cache->save($item);
                }

                return $response;
            }
        );
        $this->addMiddlewares($router, array_reverse($config->getMiddlewares()));
    }

    private function addMiddlewares(RouteInterface $router, array $middlewares): void
    {
        if (count($middlewares) === 0) {
            return;
        }
        /** @var string $middleware */
        foreach ($middlewares as $middleware) {
            $router->add($middleware);
        }
    }
}
