<?php

/**
 * MIT License
 *
 * Copyright (c) 2020 Wolf Utz<wpu@hotmail.de>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace OmegCaode\JwtSecuredApiCore;

use OmegCaode\JwtSecuredApiCore\Auth\JsonWebTokenAuth;
use OmegCaode\JwtSecuredApiCore\Factory\ContainerFactory;
use OmegCaode\JwtSecuredApiCore\Service\ConfigurationFileService;
use OmegCaode\JwtSecuredApiCore\Service\ControllerAnnotationService;
use Exception;
use Slim\App as API;
use Slim\Factory\AppFactory;
use Slim\ResponseEmitter;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Kernel
{
    protected ContainerBuilder $container;

    protected API $api;

    public function __construct()
    {
        $this->init();
    }

    public function run(): void
    {
        try {
            /** @var ConfigurationFileService $configurationFileService */
            $configurationFileService = $this->container->get(ConfigurationFileService::class);
            /** @var JsonWebTokenAuth $auth */
            $auth = $this->container->get(JsonWebTokenAuth::class);
            /** @var ControllerAnnotationService $controllerAnnotationService */
            $controllerAnnotationService = $this->container->get(ControllerAnnotationService::class);
            $router = new Router($this->api, $configurationFileService, $controllerAnnotationService, $auth);
            $router->registerRoutes($this->container);
            $this->api->run();
        } catch (Exception $exception) {
            if ((bool) $_ENV['SHOW_ERRORS']) {
                throw $exception;
            }
            $this->emitServerErrorResponse();
        }
    }

    private function init(): void
    {
        $this->container = ContainerFactory::build();
        $this->container->compile(true);
        $this->api = AppFactory::create();
        $this->api->addBodyParsingMiddleware();
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
