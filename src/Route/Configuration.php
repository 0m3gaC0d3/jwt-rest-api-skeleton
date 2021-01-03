<?php

/**
 * MIT License
 *
 * Copyright (c) 2021 Wolf Utz<wpu@hotmail.de>
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

use OmegaCode\JwtSecuredApiCore\Middleware\CacheableMiddlewareInterface;

class Configuration implements \Serializable
{
    private string $name;

    private string $action;

    private string $route;

    private bool $disabled;

    private array $allowedMethods;

    private array $middlewares;

    public function __construct(
        string $name,
        string $action,
        string $route,
        bool $disabled,
        array $allowedMethods,
        array $middlewares
    ) {
        $this->name = $name;
        $this->action = $action;
        $this->route = $route;
        $this->disabled = $disabled;
        $this->allowedMethods = $allowedMethods;
        $this->middlewares = $middlewares;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): void
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

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;
    }

    public function isCacheable(): bool
    {
        foreach ($this->middlewares as $middleware) {
            $interfaces = (array) class_implements($middleware);
            if (isset($interfaces[CacheableMiddlewareInterface::class])) {
                return true;
            }
        }

        return false;
    }

    public function serialize(): string
    {
        return serialize([
            $this->name,
            $this->action,
            $this->route,
            $this->disabled,
            $this->allowedMethods,
            $this->middlewares,
        ]);
    }

    /**
     * @param string $data
     */
    public function unserialize($data): void
    {
        $data = unserialize($data);
        $this->name = $data[0];
        $this->action = $data[1];
        $this->route = $data[2];
        $this->disabled = (bool) $data[3];
        $this->allowedMethods = $data[4];
        $this->middlewares = $data[5];
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
