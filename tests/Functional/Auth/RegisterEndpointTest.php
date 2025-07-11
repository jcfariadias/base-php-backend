<?php

declare(strict_types=1);

namespace App\Tests\Functional\Auth;

use App\Tests\Functional\ApiTestCase;

/**
 * Functional tests for the register endpoint (POST /api/auth/register)
 * 
 * This test class follows the Single Responsibility Principle by
 * focusing solely on testing the user registration functionality.
 */
class RegisterEndpointTest extends ApiTestCase
{
    /**
     * @test
     */
    public function register_with_valid_data_returns_access_token(): void
    {
        // Act
        $this->makeJsonRequest('POST', '/api/auth/register', [
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'firstName' => 'John',
            'lastName' => 'Doe'
        ]);

        // Assert
        $content = $this->assertJsonResponse(201);
        $this->assertAuthResponse($content);
        $this->assertArrayHasKey('user', $content);
        $this->assertUserResponse($content['user']);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('User registered successfully', $content['message']);
    }

    /**
     * @test
     */
    public function register_with_invalid_email_returns_validation_error(): void
    {
        // Act
        $this->makeJsonRequest('POST', '/api/auth/register', [
            'email' => 'invalid-email',
            'password' => 'password123',
            'firstName' => 'John',
            'lastName' => 'Doe'
        ]);

        // Assert
        $this->assertValidationError(['email']);
    }

    /**
     * @test
     */
    public function register_with_missing_fields_returns_validation_error(): void
    {
        // Act
        $this->makeJsonRequest('POST', '/api/auth/register', [
            'email' => 'test@example.com'
        ]);

        // Assert
        $this->assertValidationError(['password', 'firstName', 'lastName']);
    }

    /**
     * @test
     */
    public function register_with_tenant_id_returns_success(): void
    {
        // Act
        $this->makeJsonRequest('POST', '/api/auth/register', [
            'email' => 'tenant-user@example.com',
            'password' => 'password123',
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'tenantId' => 'tenant-123'
        ]);

        // Assert
        $content = $this->assertJsonResponse(201);
        $this->assertAuthResponse($content);
    }
}