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

namespace OmegaCode\JwtSecuredApiCore\Auth;

use Cake\Chronos\Chronos;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use Ramsey\Uuid\Uuid;

class JsonWebTokenAuth implements JsonWebTokenAuthInterface
{
    private string $issuer;

    private int $lifetime;

    private string $privateKey;

    private string $publicKey;

    private Sha256 $signer;

    public function __construct(
        string $issuer,
        int $lifetime,
        string $privateKey,
        string $publicKey
    ) {
        $this->issuer = $issuer;
        $this->lifetime = $lifetime;
        $this->signer = new Sha256();
        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;



         echo $this->privateKey;

         die();
        $this->loadKeysIfNecessary();
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
        try {
            $token = $this->createParsedToken($accessToken);
            if (!$token->verify($this->signer, $this->publicKey)) {
                // Token signature is not valid
                return false;
            }
            $data = new ValidationData();
            $data->setCurrentTime(Chronos::now()->getTimestamp());
            $data->setIssuer($token->getClaim('iss'));
            $data->setId($token->getClaim('jti'));
        } catch (\Exception $e) {
            return false;
        }

        return $token->validate($data);
    }

    protected function loadKeysIfNecessary(): void
    {
        if (file_exists(APP_ROOT_PATH . $this->privateKey)) {
            $this->privateKey = (string) file_get_contents(APP_ROOT_PATH . $this->privateKey);
        }
        if (file_exists(APP_ROOT_PATH . $this->publicKey)) {
            $this->publicKey = (string) file_get_contents(APP_ROOT_PATH . $this->publicKey);
        }
    }
}
