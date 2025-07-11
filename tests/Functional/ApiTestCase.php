<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient(['environment' => 'test']);
        $this->client->disableReboot();
        
        // Initialize test database
        $this->initializeDatabase();
    }
    
    private function initializeDatabase(): void
    {
        $container = static::getContainer();
        
        // Create database schema using Doctrine schema tool
        $application = new Application($container->get('kernel'));
        $application->setAutoExit(false);
        
        // Drop existing schema (ignore errors)
        $dropInput = new ArrayInput([
            'command' => 'doctrine:schema:drop',
            '--force' => true,
            '--env' => 'test'
        ]);
        $application->run($dropInput, new NullOutput());
        
        // Create schema from entities
        $createInput = new ArrayInput([
            'command' => 'doctrine:schema:create',
            '--env' => 'test'
        ]);
        $application->run($createInput, new NullOutput());
        
        // Create test data
        $this->createTestUsers();
    }
    
    private function createTestUsers(): void
    {
        $container = static::getContainer();
        $userRepository = $container->get('App\Infrastructure\User\Repository\UserRepository');
        $passwordHasher = $container->get('Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface');
        
        // Create test user for login tests
        $testUser = new \App\Domain\User\Entity\User(
            \App\Domain\User\ValueObject\UserId::generate(),
            \App\Domain\User\ValueObject\Email::fromString('test@example.com'),
            'Test',
            'User',
            '' // temporary password
        );
        
        // Hash the password and set it
        $hashedPassword = $passwordHasher->hashPassword($testUser, 'password123');
        $testUser->setPassword($hashedPassword);
        
        $userRepository->save($testUser);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Create JSON request to API endpoint
     */
    protected function makeJsonRequest(string $method, string $uri, array $data = [], array $headers = []): void
    {
        $defaultHeaders = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ];

        $this->client->request(
            $method,
            $uri,
            [],
            [],
            array_merge($defaultHeaders, $headers),
            json_encode($data)
        );
    }

    /**
     * Make authenticated request with access token
     */
    protected function makeAuthenticatedRequest(string $method, string $uri, string $accessToken, array $data = []): void
    {
        $headers = [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $accessToken,
        ];

        $this->makeJsonRequest($method, $uri, $data, $headers);
    }

    /**
     * Assert JSON response and return decoded content
     */
    protected function assertJsonResponse(int $expectedStatusCode): array
    {
        $response = $this->client->getResponse();
        
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
        $this->assertJson($response->getContent());
        
        return json_decode($response->getContent(), true);
    }

    /**
     * Assert validation error response
     */
    protected function assertValidationError(array $expectedFields = []): array
    {
        $content = $this->assertJsonResponse(400);
        $this->assertArrayHasKey('errors', $content);
        $this->assertIsArray($content['errors']);
        
        if (!empty($expectedFields)) {
            foreach ($expectedFields as $field) {
                $found = false;
                foreach ($content['errors'] as $error) {
                    if (str_contains($error, $field . ':')) {
                        $found = true;
                        break;
                    }
                }
                $this->assertTrue($found, "Validation error for field '{$field}' not found");
            }
        }
        
        return $content;
    }

    /**
     * Authenticate user and return access token
     */
    protected function authenticateUser(string $email = 'test@example.com', string $password = 'password123'): string
    {
        $this->makeJsonRequest('POST', '/api/auth/login', [
            'email' => $email,
            'password' => $password
        ]);

        $content = $this->assertJsonResponse(200);
        $this->assertArrayHasKey('access_token', $content);
        
        return $content['access_token'];
    }

    /**
     * Register user and return access token
     */
    protected function registerUser(
        string $email = 'newuser@example.com',
        string $password = 'password123',
        string $firstName = 'John',
        string $lastName = 'Doe'
    ): string {
        $this->makeJsonRequest('POST', '/api/auth/register', [
            'email' => $email,
            'password' => $password,
            'firstName' => $firstName,
            'lastName' => $lastName
        ]);

        $content = $this->assertJsonResponse(201);
        $this->assertArrayHasKey('access_token', $content);
        
        return $content['access_token'];
    }

    /**
     * Refresh token and return new access token
     */
    protected function refreshToken(string $refreshToken): string
    {
        $this->makeJsonRequest('POST', '/api/auth/refresh', [
            'refreshToken' => $refreshToken
        ]);

        $content = $this->assertJsonResponse(200);
        $this->assertArrayHasKey('access_token', $content);
        
        return $content['access_token'];
    }

    /**
     * Assert authentication required error
     */
    protected function assertUnauthorized(): array
    {
        return $this->assertJsonResponse(401);
    }

    /**
     * Assert successful authentication response structure
     */
    protected function assertAuthResponse(array $content): void
    {
        $this->assertArrayHasKey('access_token', $content);
        $this->assertArrayHasKey('refresh_token', $content);
        $this->assertArrayHasKey('token_type', $content);
        $this->assertArrayHasKey('expires_in', $content);
        $this->assertEquals('Bearer', $content['token_type']);
        $this->assertIsString($content['access_token']);
        $this->assertIsString($content['refresh_token']);
        $this->assertIsInt($content['expires_in']);
        $this->assertGreaterThan(0, $content['expires_in']);
    }

    /**
     * Assert user data structure in response
     */
    protected function assertUserResponse(array $user): void
    {
        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('email', $user);
        $this->assertArrayHasKey('first_name', $user);
        $this->assertArrayHasKey('last_name', $user);
        $this->assertIsString($user['id']);
        $this->assertIsString($user['email']);
        $this->assertIsString($user['first_name']);
        $this->assertIsString($user['last_name']);
    }
}