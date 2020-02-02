<?php

declare(strict_types=1);

namespace App;

use App\Factory\ContainerFactory;
use App\Manager\RouteManager;
use App\Service\ConfigurationFileService;
use Slim\Factory\AppFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Kernel
{
    protected ContainerInterface $container;

    public function __construct()
    {
        $this->init();
    }

    public function run(): void
    {
        AppFactory::setContainer($this->container);
        $api = AppFactory::create();
        $api->addBodyParsingMiddleware();
        $routeManager = new RouteManager($api, $this->container->get(ConfigurationFileService::class));
        $routeManager->registerRoutes();
        $api->run();
    }

    private function init()
    {
        $this->container = ContainerFactory::build();
        $this->addAuthService($this->container);
        $this->container->compile();
    }

    private function addAuthService(ContainerInterface $container)
    {
        $container->set('api.auth.jwt', new \App\Auth\JsonWebTokenAuth(
            $_ENV['JWT_ISSUER'],
            (int)$_ENV['JWT_LIFETIME'],
            file_get_contents(__DIR__.'/../'.$_ENV['PRIVATE_KEY']),
            file_get_contents(__DIR__.'/../'.$_ENV['PUBLIC_KEY'])
        ));
    }
}
