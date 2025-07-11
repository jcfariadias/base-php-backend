<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Tests\Functional\ApiTestCase;

/**
 * Functional tests for the get current user endpoint (GET /api/auth/me)
 * 
 * This test class follows the Single Responsibility Principle by
 * focusing solely on testing the user profile retrieval functionality.
 */
class GetCurrentUserEndpointTest extends ApiTestCase
{
    /**
     * @test
     */
    public function get_current_user_with_valid_token_returns_user_data(): void
    {
        // Arrange
        $accessToken = $this->authenticateUser();

        // Act
        $this->makeAuthenticatedRequest('GET', '/api/auth/me', $accessToken);

        // Assert
        $content = $this->assertJsonResponse(200);
        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('email', $content);
        $this->assertArrayHasKey('first_name', $content);
        $this->assertArrayHasKey('last_name', $content);
        $this->assertArrayHasKey('roles', $content);
        $this->assertArrayHasKey('status', $content);
        $this->assertIsArray($content['roles']);
    }

    /**
     * @test
     */
    public function get_current_user_without_token_returns_unauthorized(): void
    {
        // Act
        $this->makeJsonRequest('GET', '/api/auth/me');

        // Assert
        $content = $this->assertUnauthorized();
        // The security system might return different response formats
        // Just check that we get a 401 response, which is the important part
        $this->assertIsArray($content);
    }

    /**
     * @test
     */
    public function get_current_user_with_invalid_token_returns_unauthorized(): void
    {
        // Act
        $this->makeAuthenticatedRequest('GET', '/api/auth/me', 'invalid-token');

        // Assert
        $this->assertUnauthorized();
    }
}