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

namespace OmegaCode\JwtSecuredApiCore\Provider;

use OmegaCode\JwtSecuredApiCore\Collection\RouteCollection;
use OmegaCode\JwtSecuredApiCore\Configuration\Processor\RouteConfigurationProcessor;
use OmegaCode\JwtSecuredApiCore\Factory\Route\CollectionFactory;
use OmegaCode\JwtSecuredApiCore\Service\ConfigurationFileService;
use Symfony\Component\Cache\CacheItem;

class RouteCollectionProvider extends CachableDataProvider
{
    private ConfigurationFileService $configurationFileService;

    public function __construct(ConfigurationFileService $configurationFileService)
    {
        parent::__construct();
        $this->configurationFileService = $configurationFileService;
    }

    public function getData(): RouteCollection
    {
        if (!$this->cachingIsEnabled()) {
            return $this->buildRouteCollection();
        }
        /** @var CacheItem $cacheItem */
        $cacheItem = $this->cache->getItem($this->getCacheIdentifier());
        $routeCollection = null;
        if ($cacheItem->isHit()) {
            $routeCollection = unserialize($cacheItem->get());

            return $routeCollection;
        }
        $routeCollection = $this->buildRouteCollection();
        $cacheItem->set(serialize($routeCollection));
        $this->cache->save($cacheItem);

        return $routeCollection;
    }

    public function getCacheIdentifier(): string
    {
        return 'system.routes';
    }

    private function buildRouteCollection(): RouteCollection
    {
        $configuration = $this->configurationFileService->load('routes.yaml');

        return CollectionFactory::build(
            (new RouteConfigurationProcessor())->process($configuration)
        );
    }
}
