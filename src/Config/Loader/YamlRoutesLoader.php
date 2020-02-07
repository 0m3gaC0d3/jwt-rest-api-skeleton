<?php

namespace App\Config\Loader;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Yaml\Yaml;

class YamlRoutesLoader extends FileLoader
{
    /**
     * @param mixed $resource
     *
     * @return mixed
     */
    public function load($resource, string $type = null)
    {
        if (!file_exists($resource)) {
            throw new IOException("File $resource does not exist");
        }
        $fileContent = file_get_contents($resource);
        if (is_string($fileContent) && !empty($fileContent)) {
            return Yaml::parse($fileContent);
        }
        throw new IOException("Could not read file $resource");
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, string $type = null): bool
    {
        return is_string($resource) && 'yaml' === pathinfo($resource, PATHINFO_EXTENSION);
    }
}
