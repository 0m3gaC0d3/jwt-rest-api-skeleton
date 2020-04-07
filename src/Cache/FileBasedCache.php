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

namespace OmegaCode\JwtSecuredApiCore\Cache;

use InvalidArgumentException;
use RuntimeException;

class FileBasedCache implements CacheInterface
{
    protected const CACHE_DIR = APP_ROOT_PATH . 'var/cache/request/';

    protected const FILE_EXTENSION = '.cache';

    public function set(string $identifier, string $content): void
    {
        if ($this->has($identifier)) {
            $this->remove($identifier);
        }
        if (!is_writable(static::CACHE_DIR)) {
            throw new RuntimeException('Directory "' . static::CACHE_DIR . '" is not writable');
        }
        file_put_contents(static::CACHE_DIR . $identifier . static::FILE_EXTENSION, $content);
    }

    public function get(string $identifier): string
    {
        if (!$this->has($identifier)) {
            throw new InvalidArgumentException("Cache with id \"$identifier\" does not exist");
        }
        if (!is_readable(static::CACHE_DIR . $identifier . static::FILE_EXTENSION)) {
            throw new RuntimeException('File "' . static::CACHE_DIR . static::FILE_EXTENSION . '" is not readable');
        }

        return (string) file_get_contents(static::CACHE_DIR . $identifier . static::FILE_EXTENSION);
    }

    public function has(string $identifier): bool
    {
        return file_exists(static::CACHE_DIR . $identifier . static::FILE_EXTENSION);
    }

    public function remove(string $identifier): void
    {
        if (!$this->has($identifier)) {
            throw new InvalidArgumentException("Cache with id \"$identifier\" does not exist");
        }
        if (!is_readable(static::CACHE_DIR . $identifier . static::FILE_EXTENSION)) {
            throw new RuntimeException('File "' . static::CACHE_DIR . static::FILE_EXTENSION . '" is not readable');
        }
        unlink(static::CACHE_DIR . $identifier . static::FILE_EXTENSION);
    }

    public function flush(): void
    {
        $files = (array) glob(static::CACHE_DIR . '/*');
        /** @var string $file */
        foreach ($files as $file) {
            if (is_readable($file) && is_file($file)) {
                unlink($file);
            }
        }
    }
}
