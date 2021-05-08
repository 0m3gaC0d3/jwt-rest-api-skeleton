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

namespace OmegaCode\JwtSecuredApiCore\Command\Console\Helper\Table;

use OmegaCode\JwtSecuredApiCore\Collection\RouteCollection;
use OmegaCode\JwtSecuredApiCore\Middleware\CacheableMiddlewareInterface;
use OmegaCode\JwtSecuredApiCore\Middleware\CORSMiddleware;
use OmegaCode\JwtSecuredApiCore\Middleware\JsonWebTokenMiddleware;
use OmegaCode\JwtSecuredApiCore\Middleware\RequestLoggerMiddleware;
use OmegaCode\JwtSecuredApiCore\Route\Configuration;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class RouteTable
{
    private const HEADERS = ['name', 'action', 'route', 'disabled', 'allowedMethods', 'middlewares'];

    public function render(OutputInterface $output, RouteCollection $collection): void
    {
        $rows = [];
        foreach ($collection->getAll() as $configuration) {
            $rows[] = $this->buildRow($configuration);
        }
        $this->buildTable($output, $rows)->render();
    }

    protected function buildRow(Configuration $configuration): array
    {
        $row = $configuration->toArray();
        $row['disabled'] = $row['disabled'] ? 'yes' : 'no';
        $row['allowedMethods'] = implode("\n", $row['allowedMethods']);
        $row['middlewares'] = implode("\n", array_merge($this->getSystemMiddlewares(), $row['middlewares']));

        return $row;
    }

    protected function getSystemMiddlewares(): array
    {
        $middlewares = [];
        if ((bool) $_ENV['ENABLE_JWT']) {
            $middlewares[] = JsonWebTokenMiddleware::class;
        }
        if ((bool) $_ENV['ENABLE_CORS']) {
            $middlewares[] = CORSMiddleware::class;
        }
        if ((bool) $_ENV['ENABLE_REQUEST_LOG']) {
            $middlewares[] = RequestLoggerMiddleware::class;
        }
        if ((bool) $_ENV['ENABLE_REQUEST_CACHE']) {
            $middlewares[] = CacheableMiddlewareInterface::class;
        }

        return $middlewares;
    }

    protected function buildTable(OutputInterface $output, array $rows): Table
    {
        $table = new Table($output);
        $table->setHeaders(self::HEADERS)
            ->setRows($rows);

        return $table;
    }
}
