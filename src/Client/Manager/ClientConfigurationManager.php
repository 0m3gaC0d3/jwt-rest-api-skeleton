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

namespace OmegaCode\JwtSecuredApiCore\Client\Manager;

use RuntimeException;

class ClientConfigurationManager implements ClientConfigurationManagerInterface
{
    protected string $configurationFilePath = '';

    public function __construct()
    {
        $configFile = $_ENV['APP_ENV'] === 'test' ? 'res/test/api/clients.test.json' :
            static::CONFIGURATION_FILE_NAME;
        $this->configurationFilePath = APP_ROOT_PATH . $configFile;
    }

    public function addNewClient(array $clientConfiguration): void
    {
        $configuration = $this->getConfiguration();
        $configuration = $this->addConfigurationItem($clientConfiguration, $configuration);
        $this->rebuildConfiguration($configuration);
    }

    public function getConfiguration(): array
    {
        return $this->parseConfiguration();
    }

    protected function configurationExists(): bool
    {
        return file_exists($this->configurationFilePath);
    }

    protected function rebuildConfiguration(array $content = ['clients' => []]): void
    {
        if ($this->configurationExists()) {
            unlink($this->configurationFilePath);
        }
        file_put_contents($this->configurationFilePath, json_encode($content));
    }

    protected function getConfigurationContent(): string
    {
        if (!$this->configurationExists()) {
            $this->rebuildConfiguration();
        }

        return (string) file_get_contents($this->configurationFilePath);
    }

    protected function parseConfiguration(): array
    {
        $content = $this->getConfigurationContent();

        return (array) json_decode($content, true);
    }

    protected function addConfigurationItem(array $client, array $configuration): array
    {
        if (!isset($configuration['clients'])) {
            throw new RuntimeException('Could not find client property of clients.json. The file maybe corrupt!');
        }
        $configuration['clients'][] = $client;

        return $configuration;
    }
}
