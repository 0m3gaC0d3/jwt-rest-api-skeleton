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

namespace OmegaCode\JwtSecuredApiCore\Middleware;

use OmegaCode\JwtSecuredApiCore\Factory\CacheAdapterFactory;
use OmegaCode\JwtSecuredApiCore\Generator\RequestIDGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App as API;
use Symfony\Component\Cache\Adapter\AbstractAdapter;

class CacheableHTMLMiddleware implements CacheableMiddlewareInterface
{
    protected AbstractAdapter $cache;

    protected API $api;

    public function __construct(API $api)
    {
        $this->cache = CacheAdapterFactory::build();
        $this->api = $api;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!(bool) $_ENV['ENABLE_REQUEST_CACHE']) {
            return $handler->handle($request);
        }
        $identifier = RequestIDGenerator::generate($request);
        $item = $this->cache->getItem($identifier);
        if ($item->isHit()) {
            $response = $this->api->getResponseFactory()->createResponse();
            $response->getBody()->write($item->get());
            $response = $response->withStatus(200)->withHeader('Content-type', 'text/html');

            return $response;
        }

        return $handler->handle($request);
    }
}
