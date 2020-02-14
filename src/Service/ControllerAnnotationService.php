<?php

/**
 * MIT License
 *
 * Copyright (c) 2020 Wolf Utz<wpu@hotmail.de>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace OmegaCode\JwtSecuredApiCore\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Exception;
use OmegaCode\JwtSecuredApiCore\Annotation\ControllerAnnotation;
use ReflectionClass;
use ReflectionMethod;

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
            if (!class_exists($controllerClass)) {
                throw new Exception("Controller $controllerClass does not exist");
            }
            $reflectionClass = new ReflectionClass($controllerClass);
            $reflectionMethods = $reflectionClass->getMethods();
            foreach ($reflectionMethods as $reflectionMethod) {
                $configuration = $this->getMethodAnnotationData($reflectionMethod, $configuration);
            }
        }

        return $configuration;
    }

    private function getMethodAnnotationData(ReflectionMethod $reflectionMethod, array $configuration): array
    {
        $annotations = $this->reader->getMethodAnnotations($reflectionMethod);
        foreach ($annotations as $annotation) {
            if (!$annotation instanceof ControllerAnnotation) {
                continue;
            }
            if (isset($configuration[$reflectionMethod->class . '::' . $reflectionMethod->getName()])) {
                continue;
            }
            $configuration[$reflectionMethod->class . '::' . $reflectionMethod->getName()] = [
                'controller' => $reflectionMethod->class,
                'method' => $annotation->method,
                'route' => $annotation->route,
                'protected' => $annotation->protected,
                'action' => $reflectionMethod->getName(),
            ];
        }

        return $configuration;
    }
}
