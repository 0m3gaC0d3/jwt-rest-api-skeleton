<?php

declare(strict_types=1);

namespace App\Service;

class ConsumerValidationService
{
    private ConfigurationFileService $configurationFileService;

    public function __construct(ConfigurationFileService $configurationFileService)
    {
        $this->configurationFileService = $configurationFileService;
    }

    public function isValid(string $clientId): bool
    {
        $clientConfiguration = (array) $this->configurationFileService->load('clients.yaml')['clients'];
        if (count($clientConfiguration) === 0) {
            // todo: log no clients available
            return false;
        }
        foreach ($clientConfiguration as $client) {
            if (!isset($client['id'])) {
                continue;
            }
            if ($clientId === $client['id']) {
                // todo: log valid client id
                return true;
            }
        }
        // todo: log invalid client id
        return false;
    }
}