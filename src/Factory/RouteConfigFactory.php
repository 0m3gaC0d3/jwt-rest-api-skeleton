<?php

declare(strict_types=1);

namespace OmegaCode\JwtSecuredApiCore\Factory;

use OmegaCode\JwtSecuredApiCore\Config\RouteConfig;
use Psr\Container\ContainerInterface;
use Exception;
class RouteConfigFactory
{
    public static function build(ContainerInterface $container ,array $configuration) : RouteConfig
    {
        // TODO validate config.
        $action = trim((string)$configuration['action']);
        if (!$container->has($action)) {
            throw new Exception("Could not find controller service with id: $action");
        }
        $route = trim((string)$configuration['route']);
        $methods = (array) $configuration['methods'];
        $middlewares = (array) ($configuration['middlewares'] ?? []);
        return new RouteConfig($container->get($action), $route, $methods, $middlewares);
    }
}
