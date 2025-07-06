<?php

declare(strict_types=1);

namespace App\Tests\Unit\Interfaces\Http\User\Controller;

use App\Application\User\DTO\LoginCommand;
use App\Application\User\DTO\LoginResponse;
use App\Application\User\UseCase\LoginUseCase;
use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\UserId;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserStatus;
use App\Interfaces\Http\User\Controller\LoginController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginControllerTest extends TestCase
{
    private LoginUseCase $loginUseCase;
    private ValidatorInterface $validator;
    private SerializerInterface $serializer;
    private LoginController $controller;

    protected function setUp(): void
    {
        $this->loginUseCase = $this->createMock(LoginUseCase::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        
        $this->controller = new LoginController(
            $this->loginUseCase,
            $this->validator,
            $this->serializer
        );
    }

    public function testInvokeWithValidDataReturnsSuccessResponse(): void
    {
        // Arrange
        $requestData = '{"email": "test@example.com", "password": "password123"}';
        $request = new Request([], [], [], [], [], [], $requestData);
        
        $loginCommand = new LoginCommand('test@example.com', 'password123');
        
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($requestData, LoginCommand::class, 'json')
            ->willReturn($loginCommand);
            
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($loginCommand)
            ->willReturn(new ConstraintViolationList());
            
        $this->loginUseCase
            ->expects($this->once())
            ->method('execute')
            ->with($loginCommand)
            ->willReturn($this->createMockLoginResponse());

        // Act
        $response = $this->controller->__invoke($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testInvokeWithInvalidDataReturnsValidationError(): void
    {
        // Arrange
        $requestData = '{"email": "invalid-email", "password": ""}';
        $request = new Request([], [], [], [], [], [], $requestData);
        
        $loginCommand = new LoginCommand('invalid-email', '');
        
        $violation = new ConstraintViolation(
            'Invalid email format',
            null,
            [],
            $loginCommand,
            'email',
            'invalid-email'
        );
        $violations = new ConstraintViolationList([$violation]);
        
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($requestData, LoginCommand::class, 'json')
            ->willReturn($loginCommand);
            
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($loginCommand)
            ->willReturn($violations);
            
        $this->loginUseCase
            ->expects($this->never())
            ->method('execute');

        // Act
        $response = $this->controller->__invoke($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $content);
    }

    public function testControllerImplementsInvokeMethodOnly(): void
    {
        $reflection = new \ReflectionClass(LoginController::class);
        $publicMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        
        $methodNames = array_map(fn($method) => $method->getName(), $publicMethods);
        
        // Should only have __construct and __invoke public methods
        $this->assertContains('__construct', $methodNames);
        $this->assertContains('__invoke', $methodNames);
        
        // Should exclude inherited methods from AbstractController
        $ownMethods = array_filter($publicMethods, function($method) {
            return $method->getDeclaringClass()->getName() === LoginController::class;
        });
        
        // Count should be exactly 2 (constructor + invoke)
        $this->assertCount(2, $ownMethods);
    }
    
    private function createMockLoginResponse(): LoginResponse
    {
        // Create real value objects instead of mocks
        $userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $email = Email::fromString('test@example.com');
        $status = UserStatus::ACTIVE;
        
        $mockUser = $this->createMock(User::class);
        $mockUser->method('getId')->willReturn($userId);
        $mockUser->method('getEmail')->willReturn($email);
        $mockUser->method('getRoles')->willReturn(['ROLE_USER']);
        $mockUser->method('getStatus')->willReturn($status);
        
        $mockResponse = $this->createMock(LoginResponse::class);
        $mockResponse->method('getAccessToken')->willReturn('mock-access-token');
        $mockResponse->method('getRefreshToken')->willReturn('mock-refresh-token');
        $mockResponse->method('getExpiresIn')->willReturn(3600);
        $mockResponse->method('getUser')->willReturn($mockUser);
        
        return $mockResponse;
    }
}