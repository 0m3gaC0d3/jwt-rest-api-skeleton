<?php

declare(strict_types=1);

namespace App\Service;

use Doctrine\Common\Annotations\AnnotationReader;

class ControllerAnnotationService
{
    private ConfigurationFileService $configurationFileService;

    private AnnotationReader $reader;

    public function __construct(ConfigurationFileService $configurationFileService)
    {
        $this->configurationFileService = $configurationFileService;
        $this->reader = new AnnotationReader();
    }

    public function getConfiguration(): array
    {
        $configuration = [];
        $controllerClasses = $this->configurationFileService->load('controllers.yaml')['controllers'];
        foreach ($controllerClasses as $controllerClass) {
            $reflectionClass = new \ReflectionClass($controllerClass);
            $reflectionMethods = $reflectionClass->getMethods();
            foreach ($reflectionMethods as $reflectionMethod) {
                $configuration = $this->getMethodAnnotationData($reflectionMethod, $configuration);
            }

        }

        return $configuration;
    }

    private function getMethodAnnotationData(\ReflectionMethod $reflectionMethod, array $configuration)
    {
        $annotations = $this->reader->getMethodAnnotations($reflectionMethod);
        foreach ($annotations as $annotation) {
            if (!$annotation instanceof \App\Annotation\ControllerAnnotation) {
                continue;
            }
            if (isset($configuration[$reflectionMethod->class.'::'.$reflectionMethod->getName()])) {
                continue;
            }
            $configuration[$reflectionMethod->class.'::'.$reflectionMethod->getName()] = [
                "controller" => $reflectionMethod->class,
                "method" => $annotation->method,
                "route" => $annotation->route,
                "protected" => $annotation->protected,
                "action" => $reflectionMethod->getName()
            ];
        }

        return $configuration;
    }
}