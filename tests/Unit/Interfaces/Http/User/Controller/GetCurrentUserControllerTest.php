<?php

declare(strict_types=1);

namespace App\Tests\Unit\Interfaces\Http\User\Controller;

use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\UserId;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserStatus;
use App\Interfaces\Http\User\Controller\GetCurrentUserController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetCurrentUserControllerTest extends TestCase
{
    private GetCurrentUserController $controller;

    protected function setUp(): void
    {
        $this->controller = new GetCurrentUserController();
    }

    public function testInvokeWithAuthenticatedUserReturnsUserData(): void
    {
        // Arrange
        $user = $this->createMockUser();

        // Act
        $response = $this->controller->__invoke($user);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('email', $content);
        $this->assertArrayHasKey('first_name', $content);
        $this->assertArrayHasKey('last_name', $content);
        $this->assertArrayHasKey('roles', $content);
        $this->assertArrayHasKey('status', $content);
        
        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $content['id']);
        $this->assertEquals('test@example.com', $content['email']);
        $this->assertEquals('John', $content['first_name']);
        $this->assertEquals('Doe', $content['last_name']);
        $this->assertEquals(['ROLE_USER'], $content['roles']);
        $this->assertEquals('active', $content['status']);
    }

    public function testInvokeWithNullUserReturnsUnauthorizedError(): void
    {
        // Arrange
        $user = null;

        // Act
        $response = $this->controller->__invoke($user);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $content);
        $this->assertEquals('Not authenticated', $content['error']);
    }

    public function testInvokeWithAdminUserReturnsCorrectRoles(): void
    {
        // Arrange
        $user = $this->createMockUser(['ROLE_ADMIN', 'ROLE_USER']);

        // Act
        $response = $this->controller->__invoke($user);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(['ROLE_ADMIN', 'ROLE_USER'], $content['roles']);
    }

    public function testInvokeWithInactiveUserReturnsInactiveStatus(): void
    {
        // Arrange
        $user = $this->createMockUser(['ROLE_USER'], UserStatus::INACTIVE);

        // Act
        $response = $this->controller->__invoke($user);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('inactive', $content['status']);
    }

    public function testInvokeWithDifferentEmailReturnsCorrectEmail(): void
    {
        // Arrange
        $user = $this->createMockUser(['ROLE_USER'], UserStatus::ACTIVE, 'jane@example.com');

        // Act
        $response = $this->controller->__invoke($user);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('jane@example.com', $content['email']);
    }

    public function testControllerImplementsInvokeMethodOnly(): void
    {
        $reflection = new \ReflectionClass(GetCurrentUserController::class);
        $publicMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        
        $methodNames = array_map(fn($method) => $method->getName(), $publicMethods);
        
        // Should only have __invoke public method (no constructor in this case)
        $this->assertContains('__invoke', $methodNames);
        
        // Should exclude inherited methods from AbstractController
        $ownMethods = array_filter($publicMethods, function($method) {
            return $method->getDeclaringClass()->getName() === GetCurrentUserController::class;
        });
        
        // Count should be exactly 1 (only invoke)
        $this->assertCount(1, $ownMethods);
    }
    
    private function createMockUser(
        array $roles = ['ROLE_USER'], 
        UserStatus $status = UserStatus::ACTIVE,
        string $email = 'test@example.com'
    ): User {
        $userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $userEmail = Email::fromString($email);
        
        $mockUser = $this->createMock(User::class);
        $mockUser->method('getId')->willReturn($userId);
        $mockUser->method('getEmail')->willReturn($userEmail);
        $mockUser->method('getFirstName')->willReturn('John');
        $mockUser->method('getLastName')->willReturn('Doe');
        $mockUser->method('getRoles')->willReturn($roles);
        $mockUser->method('getStatus')->willReturn($status);
        
        return $mockUser;
    }
}