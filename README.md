# Simple JWT secured API skeleton based on Slim and symfony components

## How to add a new endpoint

* Create a new class and implement the interface `\PSVneo\Request\ApiRequestInterface`.
```php
<?php

namespace PSVneo\Request;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PSVneo\Request\ApiRequestInterface;
use Slim\App as API;

class MyNewEndpointRequest implements ApiRequestInterface
{
    public function handleRequest(API $api, string $route) : void
    {
        $api->get($route, function (Request $request, Response $response, array $args) {
            $response->getBody()->write('<h1>Hello world!</h1>');

            return $response;
        });
    }
}

```
* Register your new class using the FQCN and the route (/my-endpoint) in `conf/routes.yaml`.
````yaml
routes:
  -
    class: PSVneo\Request\MyNewEndpointRequest
    route: /my-endpoint
````