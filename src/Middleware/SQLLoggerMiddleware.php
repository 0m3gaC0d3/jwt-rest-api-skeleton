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

namespace OmegaCode\JwtSecuredApiCore\Middleware;

use Monolog\Logger;
use OmegaCode\JwtSecuredApiCore\Logging\SQL\SQLLogger;
use OmegaCode\JwtSecuredApiCore\Service\DatabaseServiceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SQLLoggerMiddleware implements MiddlewareInterface
{
    protected const FILE_NAME_TEMPLATE = 'sql-log-%.log';

    protected DatabaseServiceInterface $databaseService;

    protected Logger $logger;

    public function __construct(DatabaseServiceInterface $databaseService, Logger $logger)
    {
        $this->databaseService = $databaseService;
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!((bool) $_ENV['ENABLE_SQL_LOG'])) {
            return $handler->handle($request);
        }
        $logDirectory = APP_ROOT_PATH . 'var/log/';
        $fileName = str_replace('%', (string) time(), self::FILE_NAME_TEMPLATE);
        /** @var SQLLogger $logger */
        $logger = $this->databaseService->getConnection()->getConfiguration()->getSQLLogger();
        $content = $this->buildLogContent($logger->getQueries(), $logger->getTotalTime());
        try {
            file_put_contents($logDirectory . $fileName, $content);
        } catch (\Exception $e) {
            $this->logger->error('Could not create SQL log file');
        }

        return $handler->handle($request);
    }

    protected function buildLogContent(array $queries, float $totalTime): string
    {
        $result = '';
        foreach ($queries as $query) {
            $result .= $query['sql'] . "\n";
            $result .= 'execution time: ' . $query['time'] . "\n";
            $result .= "===============================================\n";
        }
        $result .= 'Total execution time: ' . $totalTime . "\n";

        return $result;
    }
}
