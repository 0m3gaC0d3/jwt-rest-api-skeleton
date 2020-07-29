<?php

declare(strict_types=1);

use OmegaCode\JwtSecuredApiCore\Core\Kernel\HttpKernel;

(function () {
    define('APP_ROOT_PATH', dirname(__DIR__, 1) . '/');
    require APP_ROOT_PATH . 'vendor/autoload.php';
    $envFile = isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'test'  ? '.env.test' : '.env';
    (new \Symfony\Component\Dotenv\Dotenv())->loadEnv(APP_ROOT_PATH . $envFile);
    (new HttpKernel())->run();
})();
