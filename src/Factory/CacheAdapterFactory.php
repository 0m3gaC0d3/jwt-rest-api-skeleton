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

namespace OmegaCode\JwtSecuredApiCore\Factory;

use Exception;
use RuntimeException;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class CacheAdapterFactory
{
    public static function build(array $arguments = []): AbstractAdapter
    {
        try {
            $cacheClass = $_ENV['CACHE_ADAPTER_CLASS'];
            switch ($cacheClass) {
                case FilesystemAdapter::class:
                    $cache = new FilesystemAdapter('system', 0, APP_ROOT_PATH . 'var/cache/');
                    break;
                default:
                    /** @var AbstractAdapter $cache */
                    $cache = new $_ENV['CACHE_ADAPTER_CLASS'](...$arguments);
            }
        } catch (Exception $exception) {
            throw new RuntimeException('Could not instantiate cache adapter class. check your .env file.');
        }
        if (!$cache instanceof AbstractAdapter) {
            throw new RuntimeException('The given adapter is not of type ' . AbstractAdapter::class);
        }

        return $cache;
    }
}
