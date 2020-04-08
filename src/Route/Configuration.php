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

namespace OmegaCode\JwtSecuredApiCore\Route;

use OmegaCode\JwtSecuredApiCore\Action\AbstractAction;
use OmegaCode\JwtSecuredApiCore\Middleware\CacheableMiddlewareInterface;

class Configuration
{
    private string $name;

    private AbstractAction $action;

    private string $route;

    private array $allowedMethods;

    private array $middlewares;

    public function __construct(
        string $name,
        AbstractAction $action,
        string $route,
        array $allowedMethods,
        array $middlewares
    ) {
        $this->name = $name;
        $this->action = $action;
        $this->route = $route;
        $this->allowedMethods = $allowedMethods;
        $this->middlewares = $middlewares;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAction(): AbstractAction
    {
        return $this->action;
    }

    public function setAction(AbstractAction $action): void
    {
        $this->action = $action;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }

    public function setAllowedMethods(array $allowedMethods): void
    {
        $this->allowedMethods = $allowedMethods;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function setMiddlewares(array $middlewares): void
    {
        $this->middlewares = $middlewares;
    }

    public function isCacheable(): bool
    {
        foreach ($this->middlewares as $middleware) {
            $interfaces = class_implements($middleware);
            if (isset($interfaces[CacheableMiddlewareInterface::class])) {
                return true;
            }
        }

        return false;
    }
}
