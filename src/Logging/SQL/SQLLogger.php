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

namespace OmegaCode\JwtSecuredApiCore\Logging\SQL;

use OmegaCode\JwtSecuredApiCore\Utility\ArrayUtility;
use OmegaCode\JwtSecuredApiCore\Utility\StringUtility;

class SQLLogger implements \Doctrine\DBAL\Logging\SQLLogger
{
    protected float $totalTime = 0.0;

    protected array $queries = [];

    protected array $currentQuery = [];

    public function getTotalTime(): float
    {
        return $this->totalTime;
    }

    public function getQueries(): array
    {
        return $this->queries;
    }

    public function startQuery($sql, ?array $params = null, ?array $types = null): void
    {
        $this->currentQuery = [
            'sql' => $this->resolveQuery($sql, (array) $params),
            'params' => (array) $params,
            'time' => \microtime(true),
        ];
    }

    public function stopQuery(): void
    {
        $this->currentQuery['time'] = $this->microtimeFormat(
            \microtime(true) - $this->currentQuery['time']
        );
        $this->totalTime += $this->currentQuery['time'];
        $this->queries[] = $this->currentQuery;
    }

    protected function resolveQuery(string $sql, array $params): string
    {
        $result = $sql;
        $paramsIsAssocArray = ArrayUtility::isAssocArray($params);
        foreach ($params as $key => $value) {
            if (is_string($value)) {
                $value = '"' . $value . '"';
            }
            if (is_array($value)) {
                $value = '(' . implode(',', $value) . ')';
            }
            if ($paramsIsAssocArray) {
                $result = str_replace(":$key", $value, $result);
                continue;
            }
            $result = StringUtility::strReplaceFirst('?', $value, $result);
        }

        return $result;
    }

    protected function microtimeFormat(float $time)
    {
        return number_format($time * 1000, 2);
    }
}
