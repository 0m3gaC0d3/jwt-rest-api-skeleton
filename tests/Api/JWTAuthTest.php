<?php

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
        $response = $this->client->request('POST', 'http://api/auth', ['http_errors' => false,]);
        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function accessTokenRouteWithWrongSecretReturns401(): void
    {
        $response = $this->client->request('POST', 'http://api/auth', [
            'http_errors' => false,
            'json' => ["clientId" => "wrong-secret"]
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
            'json' => ["clientId" => "1234"]// @see res/test/api/clients.test.json
        ]);
        dump((string) $response->getBody());
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
            ]
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
            'json' => ["clientId" => "1234"]// @see res/test/api/clients.test.json
        ]);
        $result = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('access_token', $result);
        $token = $result['access_token'];
        $response = $this->client->request('POST', 'http://api/auth/verify', [
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ]
        ]);
        $this->assertTrue(json_decode((string) $response->getBody(), true)['success']);
    }
}
