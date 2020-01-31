<?php

declare(strict_types=1);

namespace PSVneo\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class DatabaseService
{
    public function getConnection(): Connection
    {
        return DriverManager::getConnection([
            'dbname' => $_ENV['DATABASE'],
            'user' => $_ENV['USER'],
            'password' => $_ENV['PASSWORD'],
            'host' => $_ENV['HOST'],
            'driver' => $_ENV['DRIVER'],
        ]);
    }
}
