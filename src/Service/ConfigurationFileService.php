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

namespace OmegaCode\JwtSecuredApiCore\Service;

use Exception;
use OmegaCode\JwtSecuredApiCore\Config\Loader\YamlRoutesLoader;
use OmegaCode\JwtSecuredApiCore\Constants;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;

class ConfigurationFileService
{
    public function load(string $configurationFile): array
    {
        $fileLocator = new FileLocator($this->getConfigurationFileDirectories());
        $resources = (array) $fileLocator->locate($configurationFile, null, false);
        $loaderResolver = new LoaderResolver([new YamlRoutesLoader($fileLocator)]);
        $delegatingLoader = new DelegatingLoader($loaderResolver);
        $result = [];
        foreach ($resources as $resource) {
            $result[] = $delegatingLoader->load($resource);
        }

        return array_merge_recursive(...$result);
    }

    private function getConfigurationFileDirectories(): array
    {
        if (!defined('APP_ROOT_PATH')) {
            throw new Exception('Constant APP_ROOT_PATH is not defined but required');
        }

        return [
            realpath(Constants::CONF_ROOT_PATH),
            realpath(APP_ROOT_PATH . 'conf/'),
        ];
    }
}
