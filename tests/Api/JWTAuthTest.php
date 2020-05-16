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

namespace OmegaCode\JwtSecuredApiCore\Tests\Api;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class JWTAuthTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $this->client = new Client();
    }

    /**
     * @test
     */
    public function accessTokenRouteWithOutSecretReturns401(): void
    {
        $response = $this->client->request('POST', 'http://api/auth', ['http_errors' => false]);
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function accessTokenRouteWithWrongSecretReturns401(): void
    {
        $response = $this->client->request('POST', 'http://api/auth', [
            'http_errors' => false,
            'json' => ['clientId' => 'wrong-secret'],
        ]);
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function accessTokenRouteWithCorrectSecretReturns201(): void
    {
        $response = $this->client->request('POST', 'http://api/auth', [
            'http_errors' => false,
            'json' => ['clientId' => '1234'], // @see res/test/api/clients.test.json
        ]);
        $this->assertEquals(201, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function accessTokenVerifyRouteWithWrongTokenReturnsSuccessFalse(): void
    {
        $response = $this->client->request('POST', 'http://api/auth/verify', [
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Bearer wrong-token',
                'Accept' => 'application/json',
            ],
        ]);
        $this->assertFalse(json_decode((string) $response->getBody(), true)['success']);
    }

    /**
     * @test
     */
    public function accessTokenVerifyRouteWithCorrectTokenReturnsSuccessTrue(): void
    {
        $response = $this->client->request('POST', 'http://api/auth', [
            'http_errors' => false,
            'json' => ['clientId' => '1234'], // @see res/test/api/clients.test.json
        ]);
        $result = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('access_token', $result);
        $token = $result['access_token'];
        $response = $this->client->request('POST', 'http://api/auth/verify', [
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ]);
        $this->assertTrue(json_decode((string) $response->getBody(), true)['success']);
    }
}
