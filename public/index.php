<?php
require __DIR__.'/../vendor/autoload.php';

// Enable dot env parsing
(new \Symfony\Component\Dotenv\Dotenv())->loadEnv(__DIR__.'/../.env');

// Initialise DI container
$containerBuilder = new \Symfony\Component\DependencyInjection\ContainerBuilder();
$loader = new \Symfony\Component\DependencyInjection\Loader\YamlFileLoader(
    $containerBuilder,
    new \Symfony\Component\Config\FileLocator(__DIR__.'/../conf')
);
$loader->load('services.yaml');
$containerBuilder->compile();

// Run the app
$app = new \PSVneo\App($containerBuilder);
$app->run();
