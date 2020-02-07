<?php

declare(strict_types=1);

namespace App\Controller;

use App\Auth\JsonWebTokenAuth;
use App\Service\ConsumerValidationService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Annotation\ControllerAnnotation;

class AuthController
{
    private JsonWebTokenAuth $auth;

    private ConsumerValidationService $consumerValidationService;

    public function __construct(JsonWebTokenAuth $auth, ConsumerValidationService $consumerValidationService)
    {
        $this->auth = $auth;
        $this->consumerValidationService = $consumerValidationService;
    }

    /**
     * @ControllerAnnotation(route="/api/tokens", method="post", protected=false)
     */
    public function newTokenAction(Request $request, Response $response): Response
    {
        if (!$this->consumerValidationService->isValid($request)) {
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401, 'Unauthorized');
        }
        $result = [
            'access_token' => $this->auth->createJwt([]),
            'token_type' => 'Bearer',
            'expires_in' => $this->auth->getLifetime(),
        ];
        $response->getBody()->write((string) json_encode($result));
        $response = $response->withStatus(201)->withHeader('Content-type', 'application/json');

        return $response;
    }
}
