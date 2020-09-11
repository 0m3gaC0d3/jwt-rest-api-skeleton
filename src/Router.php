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

use OmegaCode\JwtSecuredApiCore\Core\Api;
use OmegaCode\JwtSecuredApiCore\Event\RouteCollectionFilledEvent;
use OmegaCode\JwtSecuredApiCore\Provider\RouteCollectionProvider;
use OmegaCode\JwtSecuredApiCore\Route\Configuration;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Router implements RouterInterface
{
    public const ALLOWED_METHODS = ['get', 'post', 'put', 'delete', 'patch', 'options'];

    private Api $api;

    private RouteCollectionProvider $routeCollectionProvider;

    private EventDispatcher $eventDispatcher;

    public function __construct(
        Api $api,
        RouteCollectionProvider $routeCollectionProvider,
        EventDispatcher $eventDispatcher
    ) {
        $this->api = $api;
        $this->routeCollectionProvider = $routeCollectionProvider;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function registerRoutes(): void
    {
        $routeCollection = $this->routeCollectionProvider->getData();
        $this->eventDispatcher->dispatch(
            new RouteCollectionFilledEvent($routeCollection),
            RouteCollectionFilledEvent::NAME
        );
        foreach ($routeCollection as $routeConfig) {
            $this->handleRoutes($routeConfig);
        }
    }

    private function handleRoutes(Configuration $config): void
    {
        /** @var string $method */
        foreach ($config->getAllowedMethods() as $method) {
            $this->api->addRoute($method, $config);
        }
    }
}
