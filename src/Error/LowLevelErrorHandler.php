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

namespace OmegaCode\JwtSecuredApiCore\Error;

class LowLevelErrorHandler extends AbstractErrorHandler
{
    private float $startTime = 0;

    public function __construct(bool $showErrors, bool $logErrors)
    {
        parent::__construct($showErrors, $logErrors);
        $this->startTime = microtime(true);
        ob_start();
        set_error_handler([$this, 'scriptError']);
        register_shutdown_function([$this, 'shutdown']);
    }

    public function scriptError(int $type, string $message, string $file, int $line): bool
    {
        if ($type === E_NOTICE) {
            return true;
        }
        if (!is_null($this->logger) && $this->logErrors) {
            $this->logger->error($message);
        }
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json');
        }
        if (ob_get_contents() !== false) {
            ob_end_clean();
        }
        echo $this->outputErrorInformation($type, $message, $file, $line);
        die();
    }

    public function shutdown(): void
    {
        $error = error_get_last();
        if (!$error) {
            return;
        }
        switch ($error['type']) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
                $this->scriptError($error['type'], $error['message'], $error['file'], $error['line']);
                break;
        }
    }

    private function outputErrorInformation(int $type, string $message, string $file, int $line): string
    {
        $severity = (static::ERROR_TYPE_MAPPING[$type] ?? 'Error');
        $response = ['status' => 500, 'message' => 'Internal server error'];
        if ($this->showErrors) {
            $response = array_merge($response, [
                'message' => $message,
                'line' => $line,
                'file' => $file,
                'backtrace' => $this->buildBacktraceInformation(),
                'severity' => $severity,
            ]);
        }

        return $this->buildJsonResponse($response);
    }
}
