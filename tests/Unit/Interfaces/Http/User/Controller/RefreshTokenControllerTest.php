<?php

declare(strict_types=1);

namespace App\Tests\Unit\Interfaces\Http\User\Controller;

use App\Application\User\DTO\RefreshTokenCommand;
use App\Application\User\DTO\RefreshTokenResponse;
use App\Application\User\UseCase\RefreshTokenUseCase;
use App\Application\User\Service\AuthenticationService;
use App\Domain\User\ValueObject\TokenPair;
use App\Interfaces\Http\User\Controller\RefreshTokenController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RefreshTokenControllerTest extends TestCase
{
    private RefreshTokenUseCase $refreshTokenUseCase;
    private ValidatorInterface $validator;
    private SerializerInterface $serializer;
    private RefreshTokenController $controller;

    protected function setUp(): void
    {
        $this->refreshTokenUseCase = $this->createMock(RefreshTokenUseCase::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        
        $this->controller = new RefreshTokenController(
            $this->refreshTokenUseCase,
            $this->validator,
            $this->serializer
        );
    }

    public function testInvokeWithValidRefreshTokenReturnsSuccessResponse(): void
    {
        // Arrange
        $requestData = '{"refreshToken": "valid-refresh-token"}';
        $request = new Request([], [], [], [], [], [], $requestData);
        
        $refreshTokenCommand = new RefreshTokenCommand('valid-refresh-token');
        
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($requestData, RefreshTokenCommand::class, 'json')
            ->willReturn($refreshTokenCommand);
            
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($refreshTokenCommand)
            ->willReturn(new ConstraintViolationList());
            
        $this->refreshTokenUseCase
            ->expects($this->once())
            ->method('execute')
            ->with($refreshTokenCommand)
            ->willReturn($this->createMockRefreshTokenResponse());

        // Act
        $response = $this->controller->__invoke($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('access_token', $content);
        $this->assertArrayHasKey('refresh_token', $content);
        $this->assertArrayHasKey('token_type', $content);
        $this->assertArrayHasKey('expires_in', $content);
        $this->assertEquals('Bearer', $content['token_type']);
    }

    public function testInvokeWithInvalidRefreshTokenReturnsValidationError(): void
    {
        // Arrange
        $requestData = '{"refreshToken": ""}';
        $request = new Request([], [], [], [], [], [], $requestData);
        
        $refreshTokenCommand = new RefreshTokenCommand('');
        
        $violation = new ConstraintViolation(
            'Refresh token cannot be empty',
            null,
            [],
            $refreshTokenCommand,
            'refreshToken',
            ''
        );
        $violations = new ConstraintViolationList([$violation]);
        
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($requestData, RefreshTokenCommand::class, 'json')
            ->willReturn($refreshTokenCommand);
            
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($refreshTokenCommand)
            ->willReturn($violations);
            
        $this->refreshTokenUseCase
            ->expects($this->never())
            ->method('execute');

        // Act
        $response = $this->controller->__invoke($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $content);
        $this->assertContains('refreshToken: Refresh token cannot be empty', $content['errors']);
    }

    public function testInvokeWithExpiredTokenThrowsExceptionReturnsUnauthorized(): void
    {
        // Arrange
        $requestData = '{"refreshToken": "expired-token"}';
        $request = new Request([], [], [], [], [], [], $requestData);
        
        $refreshTokenCommand = new RefreshTokenCommand('expired-token');
        
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($requestData, RefreshTokenCommand::class, 'json')
            ->willReturn($refreshTokenCommand);
            
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($refreshTokenCommand)
            ->willReturn(new ConstraintViolationList());
            
        $this->refreshTokenUseCase
            ->expects($this->once())
            ->method('execute')
            ->with($refreshTokenCommand)
            ->willThrowException(new \Exception('Token expired'));

        // Act
        $response = $this->controller->__invoke($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $content);
        $this->assertEquals('Invalid refresh token', $content['error']);
    }

    public function testInvokeWithMalformedJsonReturnsUnauthorized(): void
    {
        // Arrange
        $requestData = '{"refreshToken": "malformed-token"}';
        $request = new Request([], [], [], [], [], [], $requestData);
        
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($requestData, RefreshTokenCommand::class, 'json')
            ->willThrowException(new \Exception('Malformed JSON'));
            
        $this->validator
            ->expects($this->never())
            ->method('validate');
            
        $this->refreshTokenUseCase
            ->expects($this->never())
            ->method('execute');

        // Act
        $response = $this->controller->__invoke($request);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $content);
        $this->assertEquals('Invalid refresh token', $content['error']);
    }

    public function testControllerImplementsInvokeMethodOnly(): void
    {
        $reflection = new \ReflectionClass(RefreshTokenController::class);
        $publicMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        
        $methodNames = array_map(fn($method) => $method->getName(), $publicMethods);
        
        // Should only have __construct and __invoke public methods
        $this->assertContains('__construct', $methodNames);
        $this->assertContains('__invoke', $methodNames);
        
        // Should exclude inherited methods from AbstractController
        $ownMethods = array_filter($publicMethods, function($method) {
            return $method->getDeclaringClass()->getName() === RefreshTokenController::class;
        });
        
        // Count should be exactly 2 (constructor + invoke)
        $this->assertCount(2, $ownMethods);
    }
    
    private function createMockRefreshTokenResponse(): RefreshTokenResponse
    {
        $mockResponse = $this->createMock(RefreshTokenResponse::class);
        $mockResponse->method('getAccessToken')->willReturn('new-access-token');
        $mockResponse->method('getRefreshToken')->willReturn('new-refresh-token');
        $mockResponse->method('getExpiresIn')->willReturn(3600);
        
        return $mockResponse;
    }
}