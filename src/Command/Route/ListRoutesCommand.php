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

namespace OmegaCode\JwtSecuredApiCore\Command\Route;

use OmegaCode\JwtSecuredApiCore\Command\Console\Helper\Table\RouteTable;
use OmegaCode\JwtSecuredApiCore\Provider\RouteCollectionProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListRoutesCommand extends Command
{
    protected static $defaultName = 'api:route:list';

    private RouteCollectionProvider $routeCollectionProvider;

    private RouteTable $routeTable;

    public function __construct(RouteCollectionProvider $routeCollectionProvider, RouteTable $routeTable)
    {
        parent::__construct();
        $this->routeCollectionProvider = $routeCollectionProvider;
        $this->routeTable = $routeTable;
    }

    protected function configure(): void
    {
        $this->setDescription('Prints a table of route information');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->routeTable->render($output, $this->routeCollectionProvider->getData());

        return 0;
    }
}
