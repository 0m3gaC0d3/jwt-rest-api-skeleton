<?php
declare(strict_types=1);

namespace OmegaCode\JwtSecuredApiCore\Extension;

use OmegaCode\JwtSecuredApiCore\Kernel;

abstract class KernelExtension
{
    private Kernel $coreKernel;

    public function setCoreKernel(Kernel $coreKernel) : void
    {
        $this->coreKernel = $coreKernel;
    }

    abstract public function getConfigDirectory() : string;
}
