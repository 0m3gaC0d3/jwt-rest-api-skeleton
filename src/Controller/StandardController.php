<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Annotation\ControllerAnnotation;

class StandardController
{
    /**
     * @ControllerAnnotation(route="/", method="get")
     */
    public function getAction(Container $container, Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write(json_encode(['get' => true]));

        return $response;
    }

    /**
     * @ControllerAnnotation(route="/test3000", method="get")
     */
    public function testAction(Container $container, Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write(json_encode(['test' => true]));

        return $response;
    }

    /**
     * @ControllerAnnotation(route="/", method="post")
     */
    public function postAction(Container $container, Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write(json_encode(['post' => true]));

        return $response;
    }

    /**
     * @ControllerAnnotation(route="/", method="put")
     */
    public function putAction(Container $container, Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write(json_encode(['put' => true]));

        return $response;
    }

    /**
     * @ControllerAnnotation(route="/", method="delete")
     */
    public function deleteAction(Container $container, Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write(json_encode(['delete' => true]));

        return $response;
    }

    /**
     * @ControllerAnnotation(route="/", method="patch")
     */
    public function patchAction(Container $container, Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write(json_encode(['patch' => true]));

        return $response;
    }
}
