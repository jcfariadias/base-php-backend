<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Tests\Functional\ApiTestCase;

/**
 * Functional tests for the logout endpoint (POST /api/auth/logout)
 * 
 * This test class follows the Single Responsibility Principle by
 * focusing solely on testing the logout functionality.
 */
class LogoutEndpointTest extends ApiTestCase
{
    /**
     * @test
     */
    public function logout_returns_success_message(): void
    {
        // Act
        $this->makeJsonRequest('POST', '/api/auth/logout');

        // Assert
        $content = $this->assertJsonResponse(200);
        $this->assertArrayHasKey('message', $content);
        $this->assertArrayHasKey('status', $content);
        $this->assertEquals('Successfully logged out', $content['message']);
        $this->assertEquals('success', $content['status']);
    }

    /**
     * @test
     */
    public function logout_with_authentication_returns_success_message(): void
    {
        // Arrange
        $accessToken = $this->authenticateUser();

        // Act
        $this->makeAuthenticatedRequest('POST', '/api/auth/logout', $accessToken);

        // Assert
        $content = $this->assertJsonResponse(200);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Successfully logged out', $content['message']);
    }
}