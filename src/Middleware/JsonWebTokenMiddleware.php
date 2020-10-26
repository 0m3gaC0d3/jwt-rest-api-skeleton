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

namespace OmegaCode\JwtSecuredApiCore\Middleware;

use OmegaCode\JwtSecuredApiCore\Auth\JWT\JWTAuthInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App as API;
use Slim\Exception\HttpUnauthorizedException;

class JsonWebTokenMiddleware implements MiddlewareInterface
{
    private JWTAuthInterface $jsonWebTokenAuth;

    private API $api;

    public function __construct(JWTAuthInterface $jwtAuth, API $api)
    {
        $this->jsonWebTokenAuth = $jwtAuth;
        $this->api = $api;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (((bool) $_ENV['ENABLE_JWT']) === false) {
            return $handler->handle($request);
        }
        $authorization = explode(' ', (string) $request->getHeaderLine('Authorization'));
        $token = $authorization[1] ?? '';
        if (!$token || !$this->jsonWebTokenAuth->validateToken($token)) {
            throw new HttpUnauthorizedException($request);
        }
        $parsedToken = $this->jsonWebTokenAuth->createParsedToken($token);
        $request = $request->withAttribute('token', $parsedToken);

        return $handler->handle($request);
    }
}
