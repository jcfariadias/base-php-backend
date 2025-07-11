<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Functional\ApiTestCase;

class AuthenticationFlowTest extends ApiTestCase
{
    /**
     * @test
     */
    public function complete_registration_and_login_flow(): void
    {
        $email = 'integration-test@example.com';
        $password = 'password123';
        
        // Step 1: Register new user
        $this->makeJsonRequest('POST', '/api/auth/register', [
            'email' => $email,
            'password' => $password,
            'firstName' => 'Integration',
            'lastName' => 'Test'
        ]);

        $registerContent = $this->assertJsonResponse(201);
        $this->assertAuthResponse($registerContent);
        $registerAccessToken = $registerContent['access_token'];
        $registerRefreshToken = $registerContent['refresh_token'];

        // Step 2: Verify user can access protected endpoint with registration token
        $this->makeAuthenticatedRequest('GET', '/api/auth/me', $registerAccessToken);
        $meContent = $this->assertJsonResponse(200);
        $this->assertEquals($email, $meContent['email']);
        $this->assertEquals('Integration', $meContent['first_name']);
        $this->assertEquals('Test', $meContent['last_name']);

        // Step 3: Login with same credentials
        $this->makeJsonRequest('POST', '/api/auth/login', [
            'email' => $email,
            'password' => $password
        ]);

        $loginContent = $this->assertJsonResponse(200);
        $this->assertAuthResponse($loginContent);
        $loginAccessToken = $loginContent['access_token'];
        $loginRefreshToken = $loginContent['refresh_token'];

        // Step 4: Verify tokens are different between registration and login
        $this->assertNotEquals($registerAccessToken, $loginAccessToken);
        $this->assertNotEquals($registerRefreshToken, $loginRefreshToken);

        // Step 5: Verify user can access protected endpoint with login token
        $this->makeAuthenticatedRequest('GET', '/api/auth/me', $loginAccessToken);
        $meContent2 = $this->assertJsonResponse(200);
        $this->assertEquals($email, $meContent2['email']);

        // Step 6: Refresh token
        $this->makeJsonRequest('POST', '/api/auth/refresh', [
            'refreshToken' => $loginRefreshToken
        ]);

        $refreshContent = $this->assertJsonResponse(200);
        $newAccessToken = $refreshContent['access_token'];
        $newRefreshToken = $refreshContent['refresh_token'];

        // Step 7: Verify new tokens are different
        $this->assertNotEquals($loginAccessToken, $newAccessToken);
        $this->assertNotEquals($loginRefreshToken, $newRefreshToken);

        // Step 8: Verify user can access protected endpoint with refreshed token
        $this->makeAuthenticatedRequest('GET', '/api/auth/me', $newAccessToken);
        $meContent3 = $this->assertJsonResponse(200);
        $this->assertEquals($email, $meContent3['email']);

        // Step 9: Logout
        $this->makeAuthenticatedRequest('POST', '/api/auth/logout', $newAccessToken);
        $logoutContent = $this->assertJsonResponse(200);
        $this->assertEquals('Successfully logged out', $logoutContent['message']);
    }

    /**
     * @test
     */
    public function token_refresh_chain_works_multiple_times(): void
    {
        // Step 1: Login to get initial tokens
        $this->makeJsonRequest('POST', '/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $loginContent = $this->assertJsonResponse(200);
        $refreshToken = $loginContent['refresh_token'];

        // Step 2: Perform multiple token refreshes
        for ($i = 0; $i < 3; $i++) {
            $this->makeJsonRequest('POST', '/api/auth/refresh', [
                'refreshToken' => $refreshToken
            ]);

            $refreshContent = $this->assertJsonResponse(200);
            $this->assertArrayHasKey('access_token', $refreshContent);
            $this->assertArrayHasKey('refresh_token', $refreshContent);

            // Update refresh token for next iteration
            $refreshToken = $refreshContent['refresh_token'];

            // Verify access token works
            $this->makeAuthenticatedRequest('GET', '/api/auth/me', $refreshContent['access_token']);
            $this->assertJsonResponse(200);
        }
    }

    /**
     * @test
     */
    public function expired_tokens_are_rejected(): void
    {
        // Step 1: Try to use obviously invalid/expired token
        $this->makeAuthenticatedRequest('GET', '/api/auth/me', 'expired.jwt.token');
        $this->assertUnauthorized();

        // Step 2: Try to refresh with invalid token
        $this->makeJsonRequest('POST', '/api/auth/refresh', [
            'refreshToken' => 'invalid.refresh.token'
        ]);
        $content = $this->assertUnauthorized();
        $this->assertEquals('Invalid refresh token', $content['error']);
    }

    /**
     * @test
     */
    public function concurrent_login_sessions_work_independently(): void
    {
        $email = 'concurrent-test@example.com';
        $password = 'password123';

        // Step 1: Register user for this test
        $this->makeJsonRequest('POST', '/api/auth/register', [
            'email' => $email,
            'password' => $password,
            'firstName' => 'Concurrent',
            'lastName' => 'Test'
        ]);
        $this->assertJsonResponse(201);

        // Step 2: Create first login session
        $this->makeJsonRequest('POST', '/api/auth/login', [
            'email' => $email,
            'password' => $password
        ]);
        $session1 = $this->assertJsonResponse(200);

        // Step 3: Create second login session
        $this->makeJsonRequest('POST', '/api/auth/login', [
            'email' => $email,
            'password' => $password
        ]);
        $session2 = $this->assertJsonResponse(200);

        // Step 4: Verify both sessions have different tokens
        $this->assertNotEquals($session1['access_token'], $session2['access_token']);
        $this->assertNotEquals($session1['refresh_token'], $session2['refresh_token']);

        // Step 5: Verify both tokens work independently
        $this->makeAuthenticatedRequest('GET', '/api/auth/me', $session1['access_token']);
        $this->assertJsonResponse(200);

        $this->makeAuthenticatedRequest('GET', '/api/auth/me', $session2['access_token']);
        $this->assertJsonResponse(200);

        // Step 6: Refresh one session and verify other still works
        $this->makeJsonRequest('POST', '/api/auth/refresh', [
            'refreshToken' => $session1['refresh_token']
        ]);
        $refreshed1 = $this->assertJsonResponse(200);

        // Original session 2 should still work
        $this->makeAuthenticatedRequest('GET', '/api/auth/me', $session2['access_token']);
        $this->assertJsonResponse(200);

        // New refreshed session 1 should work
        $this->makeAuthenticatedRequest('GET', '/api/auth/me', $refreshed1['access_token']);
        $this->assertJsonResponse(200);
    }

    /**
     * @test
     */
    public function user_registration_with_tenant_flow(): void
    {
        $email = 'tenant-flow@example.com';
        $tenantId = 'tenant-flow-123';

        // Step 1: Register user with tenant ID
        $this->makeJsonRequest('POST', '/api/auth/register', [
            'email' => $email,
            'password' => 'password123',
            'firstName' => 'Tenant',
            'lastName' => 'User',
            'tenantId' => $tenantId
        ]);

        $registerContent = $this->assertJsonResponse(201);
        $this->assertAuthResponse($registerContent);

        // Step 2: Verify user can login
        $this->makeJsonRequest('POST', '/api/auth/login', [
            'email' => $email,
            'password' => 'password123'
        ]);

        $loginContent = $this->assertJsonResponse(200);
        $this->assertAuthResponse($loginContent);

        // Step 3: Verify user profile includes correct data
        $this->makeAuthenticatedRequest('GET', '/api/auth/me', $loginContent['access_token']);
        $userContent = $this->assertJsonResponse(200);
        $this->assertEquals($email, $userContent['email']);
        $this->assertEquals('Tenant', $userContent['first_name']);
        $this->assertEquals('User', $userContent['last_name']);
    }

    /**
     * @test
     */
    public function complete_error_handling_flow(): void
    {
        // Step 1: Try login with non-existent user
        $this->makeJsonRequest('POST', '/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ]);
        $this->assertJsonResponse(400); // Should return validation or authentication error

        // Step 2: Try accessing protected endpoint without token
        $this->makeJsonRequest('GET', '/api/auth/me');
        $content = $this->assertUnauthorized();
        $this->assertEquals('Not authenticated', $content['error']);

        // Step 3: Try refresh with no token
        $this->makeJsonRequest('POST', '/api/auth/refresh', []);
        $this->assertValidationError(['refreshToken']);

        // Step 4: Register user with invalid data
        $this->makeJsonRequest('POST', '/api/auth/register', [
            'email' => 'invalid-email',
            'password' => '',
            'firstName' => '',
            'lastName' => ''
        ]);
        $this->assertValidationError(['email', 'password', 'firstName', 'lastName']);

        // Step 5: Verify logout works even with invalid authentication
        $this->makeJsonRequest('POST', '/api/auth/logout');
        $content = $this->assertJsonResponse(200);
        $this->assertEquals('Successfully logged out', $content['message']);
    }
}