<?php

declare(strict_types=1);

namespace App;

use App\Auth\JsonWebTokenAuth;
use App\Factory\ContainerFactory;
use App\Service\ConfigurationFileService;
use App\Service\ControllerAnnotationService;
use OmegaCode\DebuggerUtility;
use Slim\Factory\AppFactory;
use Slim\ResponseEmitter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Slim\App as API;
use Exception;

class Kernel
{
    protected ContainerBuilder $container;

    protected API $api;

    public function __construct()
    {
        $this->init();
    }

    private function init(): void
    {
        $this->container = ContainerFactory::build();
        $this->container->compile(true);
        $this->api = AppFactory::create();
        $this->api->addBodyParsingMiddleware();
    }

    public function run(): void
    {
        try {
            /** @var ConfigurationFileService $configurationFileService */
            $configurationFileService = $this->container->get(ConfigurationFileService::class);
            $auth = $this->container->get(JsonWebTokenAuth::class);
            /** @var ControllerAnnotationService $controllerAnnotationService */
            $controllerAnnotationService = $this->container->get(ControllerAnnotationService::class);
            $router = new Router($this->api, $configurationFileService, $controllerAnnotationService, $auth);
            $router->registerRoutes($this->container);
            $this->api->run();
        } catch (Exception $exception) {
            if ((bool)$_ENV['SHOW_ERRORS']) {
                throw $exception;
            }
            $this->emitServerErrorResponse();
        }
    }

    private function emitServerErrorResponse(): void
    {
        $response = $this->api->getResponseFactory()->createResponse()
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(500, 'Server Error');
        $responseEmitter = new ResponseEmitter();
        $responseEmitter->emit($response);
        die();
    }
}
