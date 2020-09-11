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

namespace OmegaCode\JwtSecuredApiCore\Core\Kernel;

use InvalidArgumentException;
use OmegaCode\JwtSecuredApiCore\Auth\JWT\JWTAuthInterface;
use OmegaCode\JwtSecuredApiCore\Error\AbstractErrorHandler;
use OmegaCode\JwtSecuredApiCore\Error\LowLevelErrorHandler;
use OmegaCode\JwtSecuredApiCore\Event\Kernel\PostKernelInitializationEvent;
use OmegaCode\JwtSecuredApiCore\Event\Kernel\PreKernelInitializationEvent;
use OmegaCode\JwtSecuredApiCore\Factory\ApiFactory;
use OmegaCode\JwtSecuredApiCore\Factory\JWTAuthFactory;
use OmegaCode\JwtSecuredApiCore\Factory\LoggerFactory;
use OmegaCode\JwtSecuredApiCore\Router;
use OmegaCode\JwtSecuredApiCore\Service\ConfigurationFileService;
use Psr\Log\LoggerInterface;
use Slim\App as API;
use Symfony\Component\EventDispatcher\EventDispatcher;

class HttpKernel extends AbstractKernel
{
    protected API $api;

    private LoggerInterface $logger;

    public function __construct()
    {
        if (!defined('APP_ROOT_PATH')) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json');
            echo (bool) $_ENV['SHOW_ERRORS'] ? '{"status": 500, "message": "Constant APP_ROOT_PATH is not defined"}' :
                AbstractErrorHandler::DEFAULT_RESPONSE;
            die();
        }
        (new LowLevelErrorHandler((bool) $_ENV['SHOW_ERRORS'], false));
        parent::__construct();
    }

    public function run(): void
    {
        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $this->container->get(EventDispatcher::class);
        $eventDispatcher->dispatch(
            new PreKernelInitializationEvent($this->container),
            PreKernelInitializationEvent::NAME
        );
        /** @var ConfigurationFileService $configurationFileService */
        $configurationFileService = $this->container->get(ConfigurationFileService::class);
        $configurationFileService->addConfigurationDirectories($this->additionalConfigurationDirectories);
        /** @var Router $router */
        $router = $this->container->get(Router::class);
        $router->registerRoutes();
        $eventDispatcher->dispatch(
            new PostKernelInitializationEvent($this->api),
            PostKernelInitializationEvent::NAME
        );
        $this->api->run();
    }

    protected function initialize(): void
    {
        parent::initialize();
        $this->logger = LoggerFactory::build();
        $this->api = ApiFactory::build($this->container, $this->logger);
        $this->container->set(JWTAuthInterface::class, $this->getConcreteAuth());
        $this->container->set(get_class($this->logger), $this->logger);
        $this->container->set(get_class($this->api), $this->api);
    }

    protected function getConcreteAuth(): JWTAuthInterface
    {
        $supportedSigners = ['RSA', 'HMAC'];
        $signer = strtoupper(trim($_ENV['JWT_SIGNER']));
        switch ($signer) {
            case 'RSA':
                return JWTAuthFactory::buildRsaAuth();
            case 'HMAC':
                return JWTAuthFactory::buildHmacAuth();
            default:
                throw new InvalidArgumentException("The given signer \"$signer\" is not supported. Supported signers are: " . implode(', ', $supportedSigners));
        }
    }
}
