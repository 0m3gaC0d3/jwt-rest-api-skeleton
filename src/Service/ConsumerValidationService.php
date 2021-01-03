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

namespace OmegaCode\JwtSecuredApiCore\Service;

use OmegaCode\JwtSecuredApiCore\Client\Manager\ClientConfigurationManagerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class ConsumerValidationService implements ConsumerValidationServiceInterface
{
    protected ClientConfigurationManagerInterface $clientConfigurationManager;

    public function __construct(ClientConfigurationManagerInterface $clientConfigurationManager)
    {
        $this->clientConfigurationManager = $clientConfigurationManager;
    }

    public function isValid(Request $request): bool
    {
        return is_array($this->getClientConfigurationByRequest($request));
    }

    public function getClientConfigurationByRequest(Request $request): ?array
    {
        $remoteAddress = $request->getServerParams()['REMOTE_ADDR'];
        $requestClientId = $this->getRequestClientId($request);
        $allowedClients = $this->clientConfigurationManager->getConfiguration()['clients'] ?? [];
        foreach ($allowedClients as $allowedClient) {
            if ($allowedClient['secret'] != $requestClientId) {
                continue;
            }
            if (empty($allowedClient['ip'])) {
                return $allowedClient;
            }
            if ($allowedClient['ip'] != $remoteAddress) {
                continue;
            }

            return $allowedClient;
        }

        return null;
    }

    protected function getRequestClientId(Request $request): string
    {
        $data = (array) $request->getParsedBody();

        return (string) ($data['clientId'] ?? '');
    }
}
