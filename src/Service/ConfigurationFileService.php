<?php

declare(strict_types=1);

namespace App\Service;

use App\Config\Loader\YamlRoutesLoader;
use App\Constants;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;

class ConfigurationFileService
{
    private const CONFIGURATION_DIRECTORIES = [
        Constants::CONF_ROOT_PATH,
    ];

    /**
     * @return mixed
     */
    public function load(string $configurationFile)
    {
        $fileLocator = new FileLocator(self::CONFIGURATION_DIRECTORIES);
        $resource = $fileLocator->locate($configurationFile, null, true);
        $loaderResolver = new LoaderResolver([new YamlRoutesLoader($fileLocator)]);
        $delegatingLoader = new DelegatingLoader($loaderResolver);

        return $delegatingLoader->load($resource);
    }
}
