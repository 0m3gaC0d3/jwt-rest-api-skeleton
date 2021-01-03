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

namespace OmegaCode\JwtSecuredApiCore\Command\Client;

use OmegaCode\JwtSecuredApiCore\Client\Manager\ClientConfigurationManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class AddClientCommand extends Command
{
    protected static $defaultName = 'api:client:add';

    protected ClientConfigurationManagerInterface $clientConfigurationManager;

    public function __construct(ClientConfigurationManagerInterface $clientConfigurationManager)
    {
        parent::__construct();
        $this->clientConfigurationManager = $clientConfigurationManager;
    }

    protected function configure(): void
    {
        $this->setDescription('Adds a new client to the clients.json.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $questionHelper = $this->getHelper('question');
        $output->writeln('-----------------------------------------------------');
        $ipQuestion = new Question('Client ip or skip: ');
        $permissionQuestion = new Question('Client permissions (comma separated): ');
        $output->writeln('To create a new client answer the following questions');
        $ip = $questionHelper->ask($input, $output, $ipQuestion);
        $permissions = $questionHelper->ask($input, $output, $permissionQuestion);
        $clientData = $this->createClientData($ip, $permissions);
        $this->clientConfigurationManager->addNewClient($clientData);
        $output->writeln('-----------------------------------------------------');
        $this->renderResultTable($output, $clientData);

        return 0;
    }

    protected function createClientData(?string $ip, ?string $permissions): array
    {
        return [
            'ip' => $ip,
            'secret' => $this->generateSecret(),
            'permissions' => (array) explode(',', (string) $permissions),
        ];
    }

    protected function generateSecret(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    protected function renderResultTable(OutputInterface $output, array $client): void
    {
        $table = new Table($output);
        $table->setHeaders(['ip', 'secret', 'permissions'])
            ->setRows([[$client['ip'], $client['secret'], implode(', ', $client['permissions'])]]);
        $table->render();
    }
}
