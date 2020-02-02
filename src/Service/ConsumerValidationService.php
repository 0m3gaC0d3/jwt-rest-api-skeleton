<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Http\Message\ServerRequestInterface as Request;

class ConsumerValidationService
{
    public function isValid(Request $request): bool
    {
        $requestClientId = $this->getRequestClientId($request);
        $clientConfiguration = (array) explode(',', $_ENV['CLIENT_IDS']);
        if (0 === count($clientConfiguration)) {
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
