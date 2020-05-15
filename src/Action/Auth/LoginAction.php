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

namespace OmegaCode\JwtSecuredApiCore\Action\Auth;

use OmegaCode\JwtSecuredApiCore\Action\AbstractAction;
use OmegaCode\JwtSecuredApiCore\Auth\JWT\JWTAuthInterface;
use OmegaCode\JwtSecuredApiCore\Service\ConsumerValidationService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpUnauthorizedException;

class LoginAction extends AbstractAction
{
    private JWTAuthInterface $auth;

    private ConsumerValidationService $consumerValidationService;

    public function __construct(JWTAuthInterface $auth, ConsumerValidationService $consumerValidationService)
    {
        $this->auth = $auth;
        $this->consumerValidationService = $consumerValidationService;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        if (!$this->consumerValidationService->isValid($request)) {
            throw new HttpUnauthorizedException($request);
        }
        $clientConfiguration = $this->consumerValidationService->getClientConfigurationByRequest($request);
        $result = [
            'access_token' => $this->auth->createJwt(['permissions' => $clientConfiguration['permissions'] ?? []]),
            'token_type' => 'Bearer',
            'expires_in' => $this->auth->getLifetime(),
        ];
        $response->getBody()->write((string) json_encode($result));
        $response = $response->withStatus(201)->withHeader('Content-type', 'application/json');

        return $response;
    }
}
