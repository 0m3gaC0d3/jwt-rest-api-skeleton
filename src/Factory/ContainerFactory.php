<?php

declare(strict_types=1);

namespace App\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

final class ContainerFactory
{
    private const CONFIGURATION_FILE_PATH = __DIR__ . '/../../conf';
    private const CONFIGURATION_FILE_NAME = 'services.yaml';

    public static function build(): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new YamlFileLoader(
            $containerBuilder,
            new FileLocator(self::CONFIGURATION_FILE_PATH)
        );
        $loader->load(self::CONFIGURATION_FILE_NAME);

        return $containerBuilder;
    }
}
