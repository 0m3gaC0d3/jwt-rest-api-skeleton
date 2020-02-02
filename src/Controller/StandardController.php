<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ControllerAnnotationService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Annotation\ControllerAnnotation;

class StandardController
{
    private ControllerAnnotationService $controllerAnnotationService;

    public function __construct(ControllerAnnotationService $controllerAnnotationService)
    {
        $this->controllerAnnotationService = $controllerAnnotationService;
    }

    /**
     * @ControllerAnnotation(route="/", method="get", protected=false)
     */
    public function getAction(Request $request, Response $response, array $args): Response
    {
        $configuration = $this->controllerAnnotationService->getConfiguration();
        ob_start();
        include __DIR__.'/../../template/welcome.php';
        $content = ob_get_contents();
        ob_end_clean();
        $response->getBody()->write($content);

        return $response;
    }
}
