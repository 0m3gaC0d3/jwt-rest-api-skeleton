# Simple JWT secured API skeleton
This is a simple jwt based API skeleton to kick start your API development.
It is based on the PHP micro framework [Slim 4](http://www.slimframework.com/)
 and some well known [Symfony 5](https://symfony.com/) components.

The skeleton comes also bundled with [DI (dependency injection)](https://symfony.com/doc/current/components/dependency_injection.html)
 and [Doctrine DBAL](https://www.doctrine-project.org/projects/doctrine-dbal/en/2.10/index.html).

## Requirements
* PHP 7.4+
* composer
* openssl
* PHP extension ext-json

## How to install
* run `composer create-project omegacode/jwt-secured-api-skeleton`.
* move `.env.dist` to `.env` and adjust the values to your needs.
* Generate a public and a private key and move them to `keys/` (You can also adjust the path in the .env file).

### Generate private key
```shell script
openssl genrsa -out private.pem 2048
```

### Generate public key
```shell script
openssl rsa -in private.pem -outform PEM -pubout -out public.pem
```

## How to configure allowed clients / add client ids.
Simple add your client ids to your `.env`:
````dotenv
CLIENT_IDS="sample-uid-1,sample-uid-2"
````
The client need this id to authenticate itself to your api.

## How to add a new endpoint
* Create a new controller class like this.
```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Annotation\ControllerAnnotation;

class MyController
{
    /**
     * @ControllerAnnotation(route="/", method="get", protected=true)
     */
    public function someAction(Container $container, Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write("Hello world");

        return $response;
    }
}
```
* Register your new controller class using the FQCN in `conf/controllers.yaml`.
````yaml
controllers:
  - App\Controller\MyController
````
