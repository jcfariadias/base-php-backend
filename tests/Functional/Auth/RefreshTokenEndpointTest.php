<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Tests\Functional\ApiTestCase;

/**
 * Functional tests for the refresh token endpoint (POST /api/auth/refresh)
 * 
 * This test class follows the Single Responsibility Principle by
 * focusing solely on testing the token refresh functionality.
 */
class RefreshTokenEndpointTest extends ApiTestCase
{
    /**
     * @test
     */
    public function refresh_token_with_valid_token_returns_new_tokens(): void
    {
        // Arrange - first login to get a refresh token
        $this->makeJsonRequest('POST', '/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        $loginContent = $this->assertJsonResponse(200);
        $refreshToken = $loginContent['refresh_token'];

        // Act
        $this->makeJsonRequest('POST', '/api/auth/refresh', [
            'refreshToken' => $refreshToken
        ]);

        // Assert
        $content = $this->assertJsonResponse(200);
        $this->assertArrayHasKey('access_token', $content);
        $this->assertArrayHasKey('refresh_token', $content);
        $this->assertArrayHasKey('token_type', $content);
        $this->assertArrayHasKey('expires_in', $content);
        
        // New tokens should be valid (they might be the same if generated at the same time)
        $this->assertNotEmpty($content['access_token']);
        $this->assertNotEmpty($content['refresh_token']);
    }

    /**
     * @test
     */
    public function refresh_token_with_invalid_token_returns_unauthorized(): void
    {
        // Act
        $this->makeJsonRequest('POST', '/api/auth/refresh', [
            'refreshToken' => 'invalid-refresh-token'
        ]);

        // Assert
        $content = $this->assertUnauthorized();
        $this->assertArrayHasKey('error', $content);
        $this->assertEquals('Invalid refresh token', $content['error']);
    }

    /**
     * @test
     */
    public function refresh_token_with_empty_token_returns_validation_error(): void
    {
        // Act
        $this->makeJsonRequest('POST', '/api/auth/refresh', [
            'refreshToken' => ''
        ]);

        // Assert
        $this->assertValidationError(['refreshToken']);
    }
}