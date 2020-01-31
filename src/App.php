<?php

declare(strict_types=1);

namespace PSVneo;

use PSVneo\Request\RouteManager;
use PSVneo\Service\ConfigurationFileService;
use Slim\Factory\AppFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class App
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function run(): void
    {
        $api = AppFactory::create();
        $routeManager = new RouteManager($api, $this->container->get(ConfigurationFileService::class));
        $routeManager->registerRoutes();
        $api->run();
    }
}
