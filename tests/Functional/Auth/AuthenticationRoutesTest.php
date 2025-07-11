<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Tests\Functional\ApiTestCase;

/**
 * Functional tests for HTTP method validation on authentication routes
 * 
 * This test class follows the Single Responsibility Principle by
 * focusing solely on testing HTTP method validation for authentication endpoints.
 */
class AuthenticationRoutesTest extends ApiTestCase
{
    /**
     * @test
     */
    public function invalid_http_methods_return_method_not_allowed(): void
    {
        // Test GET on login endpoint (should be POST)
        $this->client->request('GET', '/api/auth/login');
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode());

        // Test GET on register endpoint (should be POST)
        $this->client->request('GET', '/api/auth/register');
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode());

        // Test POST on me endpoint (should be GET)
        $this->client->request('POST', '/api/auth/me');
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode());

        // Test GET on logout endpoint (should be POST)
        $this->client->request('GET', '/api/auth/logout');
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode());
    }
}