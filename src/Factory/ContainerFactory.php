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

use OmegaCode\JwtSecuredApiCore\Constants;
use OmegaCode\JwtSecuredApiCore\Extension\KernelExtension;
use OmegaCode\JwtSecuredApiCore\Kernel;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class ContainerFactory
{
    private const CONFIGURATION_FILE_NAME = 'services.yaml';

    public static function build(Kernel $kernel): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addCompilerPass(new RegisterListenersPass(EventDispatcher::class));
        $containerBuilder->register(EventDispatcher::class, EventDispatcher::class);
        foreach (static::getConfigurationFileDirectories($kernel) as $resource) {
            $loader = new YamlFileLoader($containerBuilder, new FileLocator($resource));
            if (file_exists($resource . '/' . self::CONFIGURATION_FILE_NAME)) {
                $loader->load(self::CONFIGURATION_FILE_NAME);
            }
        }

        return $containerBuilder;
    }

    private static function getConfigurationFileDirectories(Kernel $kernel): array
    {
        $paths = [
            realpath(Constants::CONF_ROOT_PATH),
        ];
        if (count($kernel->getExtensions()) === 0) {
            return $paths;
        }
        /** @var KernelExtension $extension */
        foreach ($kernel->getExtensions() as $extension) {
            $paths[] = realpath($extension->getConfigDirectory());
        }

        return $paths;
    }
}
