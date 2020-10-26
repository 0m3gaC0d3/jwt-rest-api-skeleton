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

namespace OmegaCode\JwtSecuredApiCore\Error\Handler;

use Exception;
use Psr\Log\LoggerInterface;

abstract class AbstractErrorHandler
{
    public const DEFAULT_RESPONSE = '{"status":500, "message": "Internal server error"}';

    protected const ERROR_TYPE_MAPPING = [
        E_WARNING => 'Warning',
        E_NOTICE => 'Notice',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Warning',
        E_STRICT => 'Strict Standards',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_USER_DEPRECATED => 'User Deprecated',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        404 => 'User Error - Route not found',
    ];

    protected bool $showErrors = false;

    protected bool $logErrors = false;

    protected ?LoggerInterface $logger;

    public function __construct(bool $showErrors, bool $logErrors, ?LoggerInterface $logger = null)
    {
        ini_set('display_errors', 'on');
        error_reporting(E_ALL & ~E_NOTICE);
        ini_set('error_reporting', (string) (E_ALL & ~E_NOTICE));
        $this->showErrors = $showErrors;
        $this->logErrors = $logErrors;
        $this->logger = $logger;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    protected function buildBacktraceInformation(): array
    {
        $v = debug_backtrace();
        $backtrace = [];
        for ($i = 1; $i < \count($v); ++$i) {
            $backtrace[] = [
                'line' => ($v[$i]['line'] ?? 'unknown'),
                'file' => ($v[$i]['file'] ?? 'unknown'),
                'function' => ($v[$i]['function'] ?? 'unknown'),
                'class' => ($v[$i]['class'] ?? 'unknown'),
                'object' => isset($v[$i]['object']) && !empty($v[$i]['object']) ? json_encode($v[$i]['object']) : '',
            ];
        }

        return $backtrace;
    }

    protected function buildJsonResponse(array $data): string
    {
        try {
            $response = json_encode($data);
        } catch (Exception $e) {
            return static::DEFAULT_RESPONSE;
        }

        return is_string($response) ? str_replace('\n', ' ', $response) :
            '{"status":500, "message": "Internal server error"}';
    }
}
