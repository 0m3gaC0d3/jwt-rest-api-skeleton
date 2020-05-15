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

namespace OmegaCode\JwtSecuredApiCore\Factory;

use OmegaCode\JwtSecuredApiCore\Auth\JWT\HmacJWTAuth;
use OmegaCode\JwtSecuredApiCore\Auth\JWT\RsaJWTAuth;

class JWTAuthFactory
{
    public static function buildRsaAuth(): RsaJWTAuth
    {
        return new RsaJWTAuth(
            (string) $_ENV['JWT_ISSUER'],
            (int) $_ENV['JWT_LIFETIME'],
            APP_ROOT_PATH . $_ENV['PRIVATE_KEY_PATH'],
            APP_ROOT_PATH . $_ENV['PUBLIC_KEY_PATH'],
        );
    }

    public static function buildHmacAuth(): HmacJWTAuth
    {
        return new HmacJWTAuth(
            (string) $_ENV['JWT_ISSUER'],
            (int) $_ENV['JWT_LIFETIME'],
            $_ENV['KEY']
        );
    }
}
