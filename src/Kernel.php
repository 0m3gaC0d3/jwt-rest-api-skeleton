<?php

declare(strict_types=1);

namespace App;

use App\Manager\RouteManager;
use App\Service\ConfigurationFileService;
use Slim\Factory\AppFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Kernel
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function run(): void
    {
        AppFactory::setContainer($this->container);
        $api = AppFactory::create();
        $routeManager = new RouteManager($api, $this->container->get(ConfigurationFileService::class));
        $routeManager->registerRoutes();
        $api->run();
    }
}
