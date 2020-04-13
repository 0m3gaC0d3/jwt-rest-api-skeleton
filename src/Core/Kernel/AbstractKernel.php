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

namespace OmegaCode\JwtSecuredApiCore\Core\Kernel;

use OmegaCode\JwtSecuredApiCore\DependencyInjection\Compiler\CommandCompilerPass;
use OmegaCode\JwtSecuredApiCore\Factory\ContainerFactory;
use OmegaCode\JwtSecuredApiCore\Provider\ConfigurationDirectoryProvider;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractKernel
{
    protected ContainerBuilder $container;

    protected array $additionalConfigurationDirectories = [];

    public function __construct()
    {
        $this->initialize();
    }

    abstract public function run(): void;

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    protected function initialize(): void
    {
        $this->additionalConfigurationDirectories = $this->getConfigurationDirectories();
        $this->initializeServiceContainer();
    }

    protected function initializeServiceContainer(): void
    {
        $this->container = ContainerFactory::build($this->additionalConfigurationDirectories);
        $this->container->addCompilerPass(new CommandCompilerPass());
        $this->container->compile(true);
    }

    protected function getConfigurationDirectories(): array
    {
        $configurationProvider = new ConfigurationDirectoryProvider();

        return $configurationProvider->getData();
    }
}
