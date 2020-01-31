<?php

declare(strict_types=1);

namespace PSVneo\Request;

use Slim\App as API;

interface ApiRequestInterface
{
    public function handleRequest(API $api, string $route) : void;
}