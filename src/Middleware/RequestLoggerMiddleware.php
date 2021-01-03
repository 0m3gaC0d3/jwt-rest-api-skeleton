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

namespace OmegaCode\JwtSecuredApiCore\Middleware;

use DateTime;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Filesystem\Exception\IOException;

class RequestLoggerMiddleware implements MiddlewareInterface
{
    protected const LOG_FILE_NAME = 'request.log';

    protected Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $logFilePath = APP_ROOT_PATH . 'var/log/' . self::LOG_FILE_NAME;
        try {
            if (!file_exists($logFilePath)) {
                touch($logFilePath);
            }
            $this->writeToFile($logFilePath, $this->buildLogContent($request));
        } catch (\Exception $e) {
            $this->logger->error('Could not create SQL log file');
        }

        return $handler->handle($request);
    }

    protected function buildLogContent(ServerRequestInterface $request): string
    {
        $dateTime = new DateTime();
        $result = json_encode([
            'timestamp' => $dateTime->getTimestamp(),
            'dateTime' => $dateTime->format('Y-m-d H:i'),
            'uri' => $this->buildTargetUrl($request->getUri()),
            'arguments' => $this->extractRequestArguments($request),
            'method' => $request->getMethod(),
            'headers' => [
                'Content-Type' => $request->getHeader('Content-Type'),
                'User-Agent' => $request->getHeader('User-Agent'),
                'Accept' => $request->getHeader('Accept'),
                'Host' => $request->getHeader('Host'),
                'Accept-Encoding' => $request->getHeader('Accept-Encoding'),
                'Content-Length' => $request->getHeader('Content-Length'),
            ],
        ]);

        return is_string($result) ? $result : '';
    }

    protected function buildTargetUrl(UriInterface $uri): string
    {
        return $uri->getScheme() . '://' . $uri->getHost() . $uri->getPath();
    }

    /**
     * @return array|object|string|null
     */
    protected function extractRequestArguments(ServerRequestInterface $request)
    {
        $arguments = $request->getBody()->getContents();
        if (empty($arguments)) {
            $arguments = $request->getParsedBody();
        }

        return $arguments;
    }

    protected function writeToFile(string $filePath, string $content): void
    {
        $file = fopen($filePath, 'a');
        if ($file === false) {
            throw new IOException("Could not read file \"$filePath\".");
        }
        fwrite($file, $content . "\n");
        fclose($file);
    }
}
