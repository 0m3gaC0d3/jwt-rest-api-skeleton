<?php

/**
 * MIT License
 *
 * Copyright (c) 2021 Wolf Utz<wpu@hotmail.de>
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

namespace OmegaCode\JwtSecuredApiCore\Factory;

use OmegaCode\JwtSecuredApiCore\Error\Handler\ApiErrorRenderer;
use OmegaCode\JwtSecuredApiCore\Error\Renderer\ErrorRendererInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\App as API;
use Slim\Factory\AppFactory;
use Slim\Handlers\ErrorHandler;

class ApiFactory
{
    public static function build(ContainerInterface $container, LoggerInterface $logger): API
    {
        $api = AppFactory::create(null, $container);
        $api->addBodyParsingMiddleware();
        $errorMiddleware = $api->addErrorMiddleware(
            (bool) $_ENV['SHOW_ERRORS'],
            (bool) $_ENV['ENABLE_LOG'],
            (bool) $_ENV['SHOW_ERRORS']
        );
        /** @var ErrorRendererInterface $errorRenderer */
        $errorRenderer = $container->get(ApiErrorRenderer::class);
        /** @var ErrorHandler $errorHandler */
        $errorHandler = $errorMiddleware->getDefaultErrorHandler();
        $errorHandler->forceContentType($errorRenderer->getContentType());
        $errorHandler->registerErrorRenderer($errorRenderer->getContentType(), $errorRenderer);

        return $api;
    }
}
