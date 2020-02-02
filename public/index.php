<?php

require __DIR__.'/../vendor/autoload.php';

// Enable dot env parsing
(new \Symfony\Component\Dotenv\Dotenv())->loadEnv(__DIR__.'/../.env');

// Run the app
$kernel = new \App\Kernel();
$kernel->run();
