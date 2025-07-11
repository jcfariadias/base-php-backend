<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Tests\Functional\ApiTestCase;

/**
 * Functional tests for the login endpoint (POST /api/auth/login)
 * 
 * This test class follows the Single Responsibility Principle by
 * focusing solely on testing the login authentication functionality.
 */
class LoginEndpointTest extends ApiTestCase
{
    /**
     * @test
     */
    public function login_with_valid_credentials_returns_access_token(): void
    {
        // Act
        $this->makeJsonRequest('POST', '/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        // Assert
        $content = $this->assertJsonResponse(200);
        $this->assertAuthResponse($content);
        $this->assertArrayHasKey('user', $content);
        $this->assertUserResponse($content['user']);
    }

    /**
     * @test
     */
    public function login_with_invalid_credentials_returns_validation_error(): void
    {
        // Act
        $this->makeJsonRequest('POST', '/api/auth/login', [
            'email' => 'invalid-email',
            'password' => ''
        ]);

        // Assert
        $this->assertValidationError(['email', 'password']);
    }

    /**
     * @test
     */
    public function login_with_missing_data_returns_validation_error(): void
    {
        // Act
        $this->makeJsonRequest('POST', '/api/auth/login', []);

        // Assert
        $this->assertValidationError();
    }
}