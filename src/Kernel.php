<?php

declare(strict_types=1);

namespace App;

use App\Auth\JsonWebTokenAuth;
use App\Factory\ContainerFactory;
use App\Manager\RouteManager;
use App\Service\ConfigurationFileService;
use App\Service\ControllerAnnotationService;
use Slim\Factory\AppFactory;
use Slim\ResponseEmitter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Slim\App as API;
use Exception;

/**
 * TODO refactor this. SRP!
 */
class Kernel
{
    protected ContainerBuilder $container;

    public function __construct()
    {
        $this->init();
    }

    private function init(): void
    {
        $this->container = ContainerFactory::build();
        $this->container->compile();
    }

    public function run(): void
    {
        AppFactory::setContainer($this->container);
        $api = AppFactory::create();
        try {
            $api->addBodyParsingMiddleware();
            /** @var ConfigurationFileService $configurationFileService */
            $configurationFileService = $this->container->get(ConfigurationFileService::class);
            /** @var JsonWebTokenAuth $auth */
            $auth = $this->container->get(JsonWebTokenAuth::class);
            /** @var ControllerAnnotationService $controllerAnnotationService */
            $controllerAnnotationService = $this->container->get(ControllerAnnotationService::class);
            $routeManager = new RouteManager($api, $configurationFileService, $controllerAnnotationService, $auth);
            $routeManager->registerRoutes($this->container);
            $api->run();
        } catch (Exception $exception) {
            if ((bool)$_ENV['SHOW_ERRORS']) {
                throw $exception;
            }
            $this->emitServerErrorResponse($api);
        }
    }

    private function emitServerErrorResponse(API $api): void
    {
        $response = $api->getResponseFactory()->createResponse()
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(500, 'Server Error');
        $responseEmitter = new ResponseEmitter();
        $responseEmitter->emit($response);
        die();
    }
}
