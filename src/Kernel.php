<?php

declare(strict_types=1);

namespace App;

use App\Factory\ContainerFactory;
use App\Manager\RouteManager;
use App\Service\ConfigurationFileService;
use Slim\Factory\AppFactory;
use Slim\ResponseEmitter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Slim\App as API;
use Exception;

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
        try {
            $api->addBodyParsingMiddleware();
            $routeManager = new RouteManager($api, $this->container->get(ConfigurationFileService::class));
            $routeManager->registerRoutes();
            $api->run();
        } catch (Exception $exception) {
            if ((bool)$_ENV['SHOW_ERRORS']) {
                throw $exception;
            } else {
                // TODO: log error
                $this->emitServerErrorResponse($api);
            }
        }
    }

    private function init()
    {
        $this->container = ContainerFactory::build();
        $this->container->compile();
    }

    private function emitServerErrorResponse(API $api)
    {
        $response = $api->getResponseFactory()->createResponse()
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(500, 'Server Error');
        $responseEmitter = new ResponseEmitter();
        $responseEmitter->emit($response);
        die();
    }
}
