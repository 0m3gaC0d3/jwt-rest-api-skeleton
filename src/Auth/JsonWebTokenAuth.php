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

namespace OmegCaode\JwtSecuredApiCore\Auth;

use OmegCaode\JwtSecuredApiCore\Constants;
use Cake\Chronos\Chronos;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use OmegaCode\DebuggerUtility;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

final class JsonWebTokenAuth
{
    private string $issuer;

    private int $lifetime;

    private string $privateKey;

    private string $publicKey;

    private Sha256 $signer;

    public function __construct(string $issuer, int $lifetime, string $privateKeyPath, string $publicKeyPath)
    {
        $this->issuer = $issuer;
        $this->lifetime = $lifetime;
        $this->signer = new Sha256();
        $this->getKeyFileContent($privateKeyPath, $publicKeyPath);
    }

    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    public function createJwt(array $claims): string
    {
        $issuedAt = Chronos::now()->getTimestamp();
        $builder = (new Builder())->issuedBy($this->issuer)
            ->identifiedBy(Uuid::uuid4()->toString(), true)
            ->issuedAt($issuedAt)
            ->canOnlyBeUsedAfter($issuedAt)
            ->expiresAt($issuedAt + $this->lifetime);
        foreach ($claims as $name => $value) {
            $builder->withClaim($name, $value);
        }

        return (string) $builder->getToken($this->signer, new Key($this->privateKey));
    }

    public function createParsedToken(string $token): Token
    {
        return (new Parser())->parse($token);
    }

    public function validateToken(string $accessToken): bool
    {
        $token = $this->createParsedToken($accessToken);
        if (!$token->verify($this->signer, $this->publicKey)) {
            // Token signature is not valid
            return false;
        }
        $data = new ValidationData();
        $data->setCurrentTime(Chronos::now()->getTimestamp());
        $data->setIssuer($token->getClaim('iss'));
        $data->setId($token->getClaim('jti'));

        return $token->validate($data);
    }

    private function getKeyFileContent(string $privateKeyPath, string $publicKeyPath): void
    {
        if (!defined('APP_ROOT_PATH')) {
            throw new \Exception("Constant APP_ROOT_PATH is not defined but required");
        }
        $privateKeyFilePath = APP_ROOT_PATH . $privateKeyPath;
        $publicKeyFilePath = APP_ROOT_PATH . $publicKeyPath;
        if (!file_exists($privateKeyFilePath) || !file_exists($publicKeyFilePath)) {
            throw new FileNotFoundException('Could not load key files');
        }
        $this->privateKey = (string) file_get_contents($privateKeyFilePath);
        $this->publicKey = (string) file_get_contents($publicKeyFilePath);
    }
}
