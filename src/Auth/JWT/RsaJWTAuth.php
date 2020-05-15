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

namespace OmegaCode\JwtSecuredApiCore\Auth\JWT;

use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class RsaJWTAuth extends AbstractJWTAuth
{
    private string $privateKeyPath;

    private string $publicKeyPath;

    public function __construct(string $issuer, int $lifetime, string $privateKeyPath, string $publicKeyPath)
    {
        parent::__construct($issuer, $lifetime);
        $this->privateKeyPath = $privateKeyPath;
        $this->publicKeyPath = $publicKeyPath;
    }

    public function getSignerKey(): Key
    {
        if (!file_exists($this->privateKeyPath)) {
            throw new FileNotFoundException('Could not find private key in path: ' . $this->privateKeyPath);
        }

        return new Key((string) file_get_contents($this->privateKeyPath));
    }

    public function getVerifyKey(): string
    {
        if (!file_exists($this->publicKeyPath)) {
            throw new FileNotFoundException('Could not find private key in path: ' . $this->publicKeyPath);
        }

        return $this->publicKeyPath;
    }

    public function getSigner(): Signer
    {
        return new Sha256();
    }
}
