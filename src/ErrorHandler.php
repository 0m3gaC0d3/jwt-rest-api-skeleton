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

namespace OmegaCode\JwtSecuredApiCore;

/**
 * TODO: add better styling
 */
class ErrorHandler
{
    private float $startTime = 0;

    public function __construct()
    {
        $this->startTime = microtime(true);
        ob_start();
        ini_set('display_errors', 'on');
        error_reporting(E_ALL);
        set_error_handler([$this, 'scriptError']);
        register_shutdown_function([$this, 'shutdown']);
    }

    public function scriptError(int $type, string $message, string $file, int $line): bool
    {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }
        if (ob_get_contents() !== false) {
            ob_end_clean();
        }
        $severity = $this->translateErrorType($type);
        $v = debug_backtrace();
        date_default_timezone_set('America/New_York');
        $date = date('Y M d H:i:s');
        $out = '
      <pre style="border-bottom:1px solid #eee;">
        ' . $date . '
        <span style="color:red;">' . $severity . ':</font> ' . $message . '
          <span style="color:#3D9700;">Line ' . $line . ': ' . $file . '</span>
        </span>
        <strong>BACKTRACE:</strong>' . PHP_EOL;
        for ($i = 1; $i < \count($v); ++$i) {
            $out .= "\tLine " . (isset($v[$i]['line']) ? $v[$i]['line'] : 'unknown') . ': ' . (isset($v[$i]['file']) ? $v[$i]['file'] : 'unknown') . PHP_EOL;
            $out .= "\t\tMore: " . PHP_EOL;
            $out .= empty(($v[$i]['function'] ?? '')) ? '' : "\t\t\t" . 'Function:' . $v[$i]['function'] ?? '';
            $out .= empty(($v[$i]['class'] ?? '')) ? '' : "\t\t\t" . 'Class:' . $v[$i]['class'] ?? '';
            $out .= empty(($v[$i]['class'] ?? '')) ? '' : "\t\t\t" . 'Object:' . json_encode(($v[$i]['object'] ?? ''));
        }
        $out .= '</span></pre>';
        echo $out;

        return true;
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

    private function translateErrorType(int $type): string
    {
        switch ($type) {
            case E_WARNING:
                return 'Warning';
            case E_NOTICE:
                return 'Notice';
            case E_COMPILE_ERROR:
                return 'Compile Error';
            case E_COMPILE_WARNING:
                return 'Compile Warning';
            case E_STRICT:
                return 'Strict Standards';
            case E_RECOVERABLE_ERROR:
                return 'Recoverable Error';
            case E_DEPRECATED:
                return 'Deprecated';
            default:
                return $this->translateUserErrorType($type);
        }
    }

    private function translateUserErrorType(int $type): string
    {
        switch ($type) {
            case E_USER_ERROR:
                return 'User Error';
            case E_USER_WARNING:
                return 'User Warning';
            case E_USER_NOTICE:
                return 'User Notice';
            case E_USER_DEPRECATED:
                return 'User Deprecated';
            default:
                return $this->translateCoreErrorType($type);
        }
    }

    private function translateCoreErrorType(int $type): string
    {
        switch ($type) {
            case E_CORE_ERROR:
                return 'Core Error';
            case E_CORE_WARNING:
                return 'Core Warning';
            default:
                return 'Error';
        }
    }
}
