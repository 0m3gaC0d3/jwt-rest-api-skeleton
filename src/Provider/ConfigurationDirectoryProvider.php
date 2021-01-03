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

namespace OmegaCode\JwtSecuredApiCore\Provider;

use OmegaCode\JwtSecuredApiCore\Composer\ExtraSectionProvider;
use RuntimeException;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\CacheItem;

class ConfigurationDirectoryProvider extends CachableDataProvider
{
    protected AbstractAdapter $cache;

    public function getData(): array
    {
        if (!$this->cachingIsEnabled()) {
            return $this->getDirectoryConfiguration();
        }
        /** @var CacheItem $cacheItem */
        $cacheItem = $this->cache->getItem($this->getCacheIdentifier());
        if ($cacheItem->isHit()) {
            $data = (array) unserialize($cacheItem->get());

            return $data;
        }
        $data = $this->getDirectoryConfiguration();
        $cacheItem->set(serialize($data));
        $this->cache->save($cacheItem);

        return $data;
    }

    public function getCacheIdentifier(): string
    {
        return 'system.configuration';
    }

    protected function getDirectoryConfiguration(): array
    {
        $result = [];
        $extraProvider = new ExtraSectionProvider(APP_ROOT_PATH . 'vendor');
        $data = $extraProvider->getData();
        foreach ($data as $path => $extra) {
            $dir = str_replace('composer.json', $extra['conf-dir'], $path);
            if (!is_dir($dir)) {
                throw new RuntimeException("Directory \"$dir\" does not exist");
            }
            $result[] = $dir;
        }

        return $result;
    }
}
