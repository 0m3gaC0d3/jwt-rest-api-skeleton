# Simple JWT secured API skeleton based on Slim and symfony components

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
     * @ControllerAnnotation(route="/", method="get")
     */
    public function someAction(Container $container, Request $request, Response $response, array $args): Response
    {
        $response->getBody()->write("Hello world");

        return $response;
    }
}
```
* Register your new controller class using the FQCN,route (/my-endpoint), method, and action in `conf/routes.yaml`.
````yaml
controllers:
  - App\Controller\MyController
````