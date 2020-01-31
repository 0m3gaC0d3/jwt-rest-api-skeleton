<?php

namespace PSVneo\Config\Loader;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

class YamlRoutesLoader extends FileLoader
{
    public function load($resource, string $type = null)
    {
        return Yaml::parse(file_get_contents($resource));
    }

    public function supports($resource, string $type = null) : bool
    {
        return is_string($resource) && 'yaml' === pathinfo($resource, PATHINFO_EXTENSION);
    }
}
