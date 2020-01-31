<?php

namespace PSVneo\Request\Standard;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PSVneo\Request\ApiRequestInterface;
use Slim\App as API;

class StandardRequest implements ApiRequestInterface
{
    public function handleRequest(API $api, string $route) : void
    {
        $api->get($route, function (Request $request, Response $response, array $args) {
            $response->getBody()->write('<h1>Server!</h1>');

            return $response;
        });
    }
}
