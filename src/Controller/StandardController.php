<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class StandardController
{
    public function getAction(Container $container, Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write(json_encode(["get" => true]));

        return $response;
    }

    public function postAction(Container $container, Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write(json_encode(["post" => true]));

        return $response;
    }

    public function putAction(Container $container, Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write(json_encode(["put" => true]));

        return $response;
    }

    public function deleteAction(Container $container, Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write(json_encode(["delete" => true]));

        return $response;
    }

    public function patchAction(Container $container, Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write(json_encode(["patch" => true]));

        return $response;
    }
}
