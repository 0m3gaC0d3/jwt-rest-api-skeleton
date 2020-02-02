<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ControllerAnnotationService;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Annotation\ControllerAnnotation;

class StandardController
{
    /**
     * @ControllerAnnotation(route="/", method="get", protected=false)
     */
    public function getAction(Container $container, Request $request, Response $response, array $args): Response
    {
        /** @var ControllerAnnotationService $controllerAnotationService */
        $controllerAnnotationService = $container->get(ControllerAnnotationService::class);
        $configuration = $controllerAnnotationService->getConfiguration();
        ob_start();
        include __DIR__.'/../../template/welcome.php';
        $content = ob_get_contents();
        ob_end_clean();
        $response->getBody()->write($content);

        return $response;
    }
}
