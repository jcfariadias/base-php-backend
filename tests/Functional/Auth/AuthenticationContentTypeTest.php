<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Tests\Functional\ApiTestCase;

/**
 * Functional tests for content type validation on authentication endpoints
 * 
 * This test class follows the Single Responsibility Principle by
 * focusing solely on testing content type responses for authentication endpoints.
 */
class AuthenticationContentTypeTest extends ApiTestCase
{
    /**
     * @test
     */
    public function endpoints_return_proper_content_type(): void
    {
        // Test login endpoint
        $this->makeJsonRequest('POST', '/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        
        $response = $this->client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));

        // Test register endpoint
        $this->makeJsonRequest('POST', '/api/auth/register', [
            'email' => 'content-test@example.com',
            'password' => 'password123',
            'firstName' => 'Test',
            'lastName' => 'User'
        ]);
        
        $response = $this->client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
    }
}