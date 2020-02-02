<?php

declare(strict_types=1);

namespace App\Controller;

use App\Auth\JsonWebTokenAuth;
use App\Service\ConsumerValidationService;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Annotation\ControllerAnnotation;

class AuthController
{
    /**
     * @ControllerAnnotation(route="/api/tokens", method="post", protected=false)
     */
    public function newTokenAction(Container $container, Request $request, Response $response, array $args): Response
    {
        /** @var JsonWebTokenAuth $auth */
        $auth = $container->get('api.auth.jwt');
        /** @var ConsumerValidationService $consumerValidationService */
        $consumerValidationService = $container->get(ConsumerValidationService::class);
        $data = (array) $request->getParsedBody();
        $clientId = (string) ($data['clientId'] ?? '');
        if (!$consumerValidationService->isValid($clientId)) {
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401, 'Unauthorized');
        }
        $result = [
            'access_token' => $auth->createJwt($clientId),
            'token_type' => 'Bearer',
            'expires_in' => $auth->getLifetime(),
        ];
        $response->getBody()->write(json_encode($result));
        $response = $response->withStatus(201)->withHeader('Content-type', 'application/json');

        return $response;
    }
}
