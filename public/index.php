<?php

declare(strict_types=1);

use OmegaCode\JwtSecuredApiCore\Core\Kernel\HttpKernel;

(function () {
    define('APP_ROOT_PATH', dirname(__DIR__, 1) . '/');
    require APP_ROOT_PATH . 'vendor/autoload.php';
    $envFile = $_ENV['APPLICATION_ENVIRONMENT'] === 'test' ? '.env.test' : '.env';
    (new \Symfony\Component\Dotenv\Dotenv())->loadEnv(APP_ROOT_PATH . $envFile);
    // Create test keys if not present.
    if ($_ENV['APPLICATION_ENVIRONMENT'] === 'test') {
        if (!file_exists(APP_ROOT_PATH . $_ENV['PRIVATE_KEY_PATH'])) {
            file_put_contents(APP_ROOT_PATH . $_ENV['PRIVATE_KEY_PATH'], getenv('PRIVATE_TEST_KEY'));
        }
        if (!file_exists(APP_ROOT_PATH . $_ENV['PUBLIC_KEY_PATH'])) {
            file_put_contents(APP_ROOT_PATH . $_ENV['PUBLIC_KEY_PATH'], getenv('PUBLIC_TEST_KEY'));
        }
    }
    (new HttpKernel())->run();
})();
