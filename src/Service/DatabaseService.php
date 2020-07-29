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

namespace OmegaCode\JwtSecuredApiCore\Service;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use OmegaCode\JwtSecuredApiCore\Logging\SQL\SQLLogger;

class DatabaseService implements DatabaseServiceInterface
{
    protected string $databaseName = '';

    protected string $user = '';

    protected string $password = '';

    protected string $host = '';

    protected string $driver = '';

    protected string $charSet = '';

    protected bool $enableLog = false;

    protected ?Connection $connection = null;

    public function __construct(
        string $databaseName,
        string $user,
        string $password,
        string $host,
        string $driver,
        string $charSet,
        bool $enableLog
    ) {
        $this->databaseName = $databaseName;
        $this->user = $user;
        $this->password = $password;
        $this->host = $host;
        $this->driver = $driver;
        $this->charSet = $charSet;
        $this->enableLog = $enableLog;
    }

    public function getConnection(): Connection
    {
        return is_null($this->connection) ? $this->createConnection() : $this->connection;
    }

    protected function createConnection(): Connection
    {
        $connection = DriverManager::getConnection([
            'dbname' => $this->databaseName,
            'user' => $this->user,
            'password' => $this->password,
            'host' => $this->host,
            'driver' => $this->driver,
            'charset' => $this->charSet,
        ], $this->createConfiguration());
        $this->connection = $connection;

        return $connection;
    }

    protected function createConfiguration(): Configuration
    {
        $configuration = new Configuration();
        if ($this->enableLog) {
            $configuration->setSQLLogger(new SQLLogger());
        }

        return $configuration;
    }
}
