<?php

declare(strict_types=1);

use OmegaCode\JwtSecuredApiCore\Core\Kernel\HttpKernel;

(function () {
    define('APP_ROOT_PATH', dirname(__DIR__, 1) . '/');
    require APP_ROOT_PATH . 'vendor/autoload.php';
    $envFile = $_ENV['APPLICATION_ENVIRONMENT'] === 'test' ? '.env.test' : '.env';
    (new \Symfony\Component\Dotenv\Dotenv())->loadEnv(APP_ROOT_PATH . $envFile);
    (new HttpKernel())->run();
})();
