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

namespace OmegaCode\JwtSecuredApiCore\Error\Handler;

use OmegaCode\JwtSecuredApiCore\Error\Renderer\ErrorRendererInterface;
use ReflectionClass;
use Throwable;

class ApiErrorRenderer extends AbstractErrorHandler implements ErrorRendererInterface
{
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        if (!is_null($this->logger) && $this->logErrors) {
            $this->logger->error($exception->getMessage());
        }

        return $this->buildJsonResponse($this->buildErrorResponseData($exception, $displayErrorDetails));
    }

    public function getContentType(): string
    {
        return 'application/json';
    }

    protected function buildErrorResponseData(Throwable $exception, bool $displayErrorDetails): array
    {
        $severity = (static::ERROR_TYPE_MAPPING[$exception->getCode()] ?? 'Error');
        $response = ['status' => $this->getStatus($exception), 'message' => $exception->getMessage()];
        $response['type'] = (new ReflectionClass($exception))->getShortName();
        if ($displayErrorDetails) {
            $response = array_merge($response, [
                'message' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
                'backtrace' => $this->buildBacktraceInformation(),
                'severity' => $severity,
            ]);
        }

        return $response;
    }

    private function getStatus(Throwable $exception): int
    {
        return $exception->getCode() > 200 && $exception->getCode() < 600 ? $exception->getCode() : 500;
    }
}
