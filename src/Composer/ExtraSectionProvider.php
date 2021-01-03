<?php

/**
 * MIT License
 *
 * Copyright (c) 2021 Wolf Utz<wpu@hotmail.de>
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

namespace OmegaCode\JwtSecuredApiCore\Composer;

use RuntimeException;
use Symfony\Component\Finder\Finder;

class ExtraSectionProvider
{
    protected string $vendorRoot = '';

    public function __construct(string $vendorRoot)
    {
        $this->vendorRoot = (string) realpath($vendorRoot);
    }

    public function getData(): array
    {
        $result = [];
        /** @var array $data */
        foreach ($this->getPackageData() as $path => $data) {
            if (isset($data['extra']['jwt-secured-api'])) {
                $result[$path] = $data['extra']['jwt-secured-api'];
            }
        }

        return $result;
    }

    private function getPackageData(): array
    {
        $result = [];
        $finder = new Finder();
        $finder->files()->in($this->vendorRoot)->name('composer.json')->followLinks();
        foreach ($finder as $file) {
            $filePath = (string) realpath($this->vendorRoot . '/' . $file->getRelativePathname());
            if (!file_exists($filePath)) {
                continue;
            }
            $data = json_decode((string) file_get_contents($filePath), true);
            if (is_null($data)) {
                throw new RuntimeException("Could not parse composer json of package \"$filePath\"");
            }
            $result[$filePath] = $data;
        }

        return array_merge($result, $this->getRootPackage());
    }

    private function getRootPackage(): array
    {
        $filePath = (string) realpath($this->vendorRoot . '/../composer.json');
        if (!file_exists($filePath)) {
            throw new RuntimeException('Could not load root composer.json');
        }
        $data = json_decode((string) file_get_contents($filePath), true);
        if (is_null($data)) {
            throw new RuntimeException('Could not parse root composer json');
        }

        return [$filePath => $data];
    }
}
