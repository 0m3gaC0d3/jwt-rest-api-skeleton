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

namespace OmegaCode\JwtSecuredApiCore\Collection;

use InvalidArgumentException;
use OmegaCode\JwtSecuredApiCore\Route\Configuration;

class RouteCollection extends AbstractRouteCollection
{
    public function add(Configuration $routeConfig): void
    {
        $this->validateConfig($routeConfig);
        $this->values[] = $routeConfig;
        $this->rewind();
    }

    public function remove(Configuration $routeConfig): void
    {
        if (!$this->contains($routeConfig)) {
            return;
        }
        unset($this->values[array_search($routeConfig, $this->values)]);
        $this->rewind();
    }

    /**
     * @return Configuration[]
     */
    public function getAll(): array
    {
        return $this->values;
    }

    public function findByName(string $name): ?Configuration
    {
        foreach ($this->values as $config) {
            if ($config->getName() === $name) {
                return $config;
            }
        }

        return null;
    }

    protected function validateConfig(Configuration $config): void
    {
        foreach ($this->values as $existingConfig) {
            if ($existingConfig->getName() === $config->getName()) {
                throw new InvalidArgumentException('Route with name ' . $config->getName() . ' already exist');
            }
            if ($existingConfig->getRoute() != $config->getRoute()) {
                continue;
            }
            if (count(array_intersect($existingConfig->getAllowedMethods(), $config->getAllowedMethods())) > 0) {
                throw new InvalidArgumentException('Duplicated routes are not allowed with intersecting http methods.' . 'Breaking route name: ' . $config->getName());
            }
        }
    }

    protected function contains(Configuration $config): bool
    {
        return in_array($config, $this->values);
    }
}
