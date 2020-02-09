<?php

declare(strict_types=1);

namespace App\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class ControllerAnnotation
{
    /**
     * @var string
     * @Required
     */
    public string $route = '';

    /**
     * @var string
     * @Required
     */
    public string $method = '';

    /**
     * @var bool
     */
    public bool $protected = false;
}
