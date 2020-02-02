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
    public $route = '';

    /**
     * @var string
     * @Required
     */
    public $method = '';

    /**
     * @var bool
     */
    public $protected = false;
}
