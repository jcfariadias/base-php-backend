<?php

declare(strict_types=1);

namespace App\Tests\Unit\Interfaces\Http\User\Controller;

use App\Application\User\DTO\RegisterCommand;
use App\Application\User\DTO\RegisterResponse;
use App\Application\User\UseCase\RegisterUseCase;
use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\UserId;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserStatus;
use App\Interfaces\Http\User\Controller\RegisterController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterControllerTest extends TestCase
{
    private RegisterUseCase $registerUseCase;
    private ValidatorInterface $validator;
    private SerializerInterface $serializer;
    private RegisterController $controller;

    protected function setUp(): void
    {
        $this->registerUseCase = $this->createMock(RegisterUseCase::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        
        $this->controller = new RegisterController(
            $this->registerUseCase,
            $this->validator,
            $this->serializer
        );
    }

    public function testInvokeWithValidDataReturnsSuccessResponse(): void
    {
        // Arrange
        $requestData = '{"email": "new@example.com", "password": "password123", "firstName": "John", "lastName": "Doe"}';
        $request = new Request([], [], [], [], [], [], $requestData);
        
        $registerCommand = new RegisterCommand(
            'new@example.com', 
            'password123', 
            'John', 
            'Doe'
        );
        
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($requestData, RegisterCommand::class, 'json')
            ->willReturn($registerCommand);
            
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($registerCommand)
            ->willReturn(new ConstraintViolationList());
            
        $this->registerUseCase
            ->expects($this->once())
            ->method('execute')
            ->with($registerCommand)
            ->willReturn($this->createMockRegisterResponse());

        // Act
        $response = $this->controller->__invoke($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('access_token', $content);
        $this->assertArrayHasKey('refresh_token', $content);
        $this->assertArrayHasKey('user', $content);
    }

    public function testInvokeWithInvalidEmailReturnsValidationError(): void
    {
        // Arrange
        $requestData = '{"email": "invalid-email", "password": "password123", "firstName": "John", "lastName": "Doe"}';
        $request = new Request([], [], [], [], [], [], $requestData);
        
        $registerCommand = new RegisterCommand(
            'invalid-email', 
            'password123', 
            'John', 
            'Doe'
        );
        
        $violation = new ConstraintViolation(
            'Invalid email format',
            null,
            [],
            $registerCommand,
            'email',
            'invalid-email'
        );
        $violations = new ConstraintViolationList([$violation]);
        
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($requestData, RegisterCommand::class, 'json')
            ->willReturn($registerCommand);
            
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($registerCommand)
            ->willReturn($violations);
            
        $this->registerUseCase
            ->expects($this->never())
            ->method('execute');

        // Act
        $response = $this->controller->__invoke($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $content);
        $this->assertContains('email: Invalid email format', $content['errors']);
    }

    public function testInvokeWithMissingFieldsReturnsValidationError(): void
    {
        // Arrange
        $requestData = '{"email": "test@example.com", "password": ""}';
        $request = new Request([], [], [], [], [], [], $requestData);
        
        $registerCommand = new RegisterCommand(
            'test@example.com', 
            '', 
            '', 
            ''
        );
        
        $violations = new ConstraintViolationList([
            new ConstraintViolation(
                'Password cannot be empty',
                null,
                [],
                $registerCommand,
                'password',
                ''
            ),
            new ConstraintViolation(
                'First name cannot be empty',
                null,
                [],
                $registerCommand,
                'firstName',
                ''
            ),
            new ConstraintViolation(
                'Last name cannot be empty',
                null,
                [],
                $registerCommand,
                'lastName',
                ''
            )
        ]);
        
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($requestData, RegisterCommand::class, 'json')
            ->willReturn($registerCommand);
            
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($registerCommand)
            ->willReturn($violations);
            
        $this->registerUseCase
            ->expects($this->never())
            ->method('execute');

        // Act
        $response = $this->controller->__invoke($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $content);
        $this->assertCount(3, $content['errors']);
    }

    public function testInvokeWithTenantIdReturnsSuccessResponse(): void
    {
        // Arrange
        $requestData = '{"email": "tenant@example.com", "password": "password123", "firstName": "Jane", "lastName": "Smith", "tenantId": "tenant-123"}';
        $request = new Request([], [], [], [], [], [], $requestData);
        
        $registerCommand = new RegisterCommand(
            'tenant@example.com', 
            'password123', 
            'Jane', 
            'Smith',
            'tenant-123'
        );
        
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($requestData, RegisterCommand::class, 'json')
            ->willReturn($registerCommand);
            
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($registerCommand)
            ->willReturn(new ConstraintViolationList());
            
        $this->registerUseCase
            ->expects($this->once())
            ->method('execute')
            ->with($registerCommand)
            ->willReturn($this->createMockRegisterResponse());

        // Act
        $response = $this->controller->__invoke($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testControllerImplementsInvokeMethodOnly(): void
    {
        $reflection = new \ReflectionClass(RegisterController::class);
        $publicMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        
        $methodNames = array_map(fn($method) => $method->getName(), $publicMethods);
        
        // Should only have __construct and __invoke public methods
        $this->assertContains('__construct', $methodNames);
        $this->assertContains('__invoke', $methodNames);
        
        // Should exclude inherited methods from AbstractController
        $ownMethods = array_filter($publicMethods, function($method) {
            return $method->getDeclaringClass()->getName() === RegisterController::class;
        });
        
        // Count should be exactly 2 (constructor + invoke)
        $this->assertCount(2, $ownMethods);
    }
    
    private function createMockRegisterResponse(): RegisterResponse
    {
        // Create real value objects instead of mocks
        $userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $email = Email::fromString('new@example.com');
        $status = UserStatus::ACTIVE;
        
        $mockUser = $this->createMock(User::class);
        $mockUser->method('getId')->willReturn($userId);
        $mockUser->method('getEmail')->willReturn($email);
        $mockUser->method('getRoles')->willReturn(['ROLE_USER']);
        $mockUser->method('getStatus')->willReturn($status);
        
        $mockResponse = $this->createMock(RegisterResponse::class);
        $mockResponse->method('getAccessToken')->willReturn('mock-access-token');
        $mockResponse->method('getRefreshToken')->willReturn('mock-refresh-token');
        $mockResponse->method('getExpiresIn')->willReturn(3600);
        $mockResponse->method('getUser')->willReturn($mockUser);
        
        return $mockResponse;
    }
}