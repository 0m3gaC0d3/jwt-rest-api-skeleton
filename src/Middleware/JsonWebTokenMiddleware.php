<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Auth\JsonWebTokenAuth;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JsonWebTokenMiddleware implements MiddlewareInterface
{
    private JsonWebTokenAuth $jsonWebTokenAuth;

    private ResponseFactoryInterface $responseFactory;

    public function __construct(JsonWebTokenAuth $jwtAuth, ResponseFactoryInterface $responseFactory)
    {
        $this->jsonWebTokenAuth = $jwtAuth;
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authorization = explode(' ', (string)$request->getHeaderLine('Authorization'));
        $token = $authorization[1] ?? '';
        if (!$token || !$this->jsonWebTokenAuth->validateToken($token)) {
            return $this->responseFactory->createResponse()
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(401, 'Unauthorized');
        }
        $parsedToken = $this->jsonWebTokenAuth->createParsedToken($token);
        $request = $request->withAttribute('token', $parsedToken);

        return $handler->handle($request);
    }
}
