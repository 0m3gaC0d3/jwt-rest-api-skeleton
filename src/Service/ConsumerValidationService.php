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

namespace OmegaCode\JwtSecuredApiCore\Service;

use Psr\Http\Message\ServerRequestInterface as Request;

class ConsumerValidationService
{
    public function isValid(Request $request): bool
    {
        $requestClientId = $this->getRequestClientId($request);
        $clientConfiguration = (array) explode(',', $_ENV['CLIENT_IDS']);
        if (count($clientConfiguration) === 0) {
            // todo: log no clients available
            return false;
        }
        foreach ($clientConfiguration as $allowedClientId) {
            if ($requestClientId === $allowedClientId) {
                // todo: log valid client id
                return true;
            }
        }
        // todo: log invalid client id
        return false;
    }

    private function getRequestClientId(Request $request): string
    {
        $data = (array) $request->getParsedBody();

        return (string) ($data['clientId'] ?? '');
    }
}
