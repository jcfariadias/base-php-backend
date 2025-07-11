<?php

declare(strict_types=1);

namespace App\Tests\Unit\Interfaces\Http\User\Controller;

use App\Application\User\DTO\RegisterResponse;
use App\Application\User\DTO\RefreshTokenResponse;
use App\Application\User\UseCase\RegisterUseCase;
use App\Application\User\UseCase\RefreshTokenUseCase;
use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\UserId;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserStatus;
use PHPUnit\Framework\TestCase;

/**
 * Helper class for creating test doubles of final classes
 */
class TestDoubleHelper
{
    /**
     * Create a mock RegisterUseCase that returns a predefined response
     */
    public static function createRegisterUseCaseStub(TestCase $testCase): RegisterUseCase
    {
        $stub = $testCase->createStub(RegisterUseCase::class);
        
        // Create a mock response
        $mockResponse = self::createMockRegisterResponse($testCase);
        
        $stub->method('execute')
             ->willReturn($mockResponse);
        
        return $stub;
    }
    
    /**
     * Create a mock RegisterUseCase that throws an exception
     */
    public static function createRegisterUseCaseExceptionStub(TestCase $testCase, \Exception $exception): RegisterUseCase
    {
        $stub = $testCase->createStub(RegisterUseCase::class);
        
        $stub->method('execute')
             ->willThrowException($exception);
        
        return $stub;
    }
    
    /**
     * Create a mock RefreshTokenUseCase that returns a predefined response
     */
    public static function createRefreshTokenUseCaseStub(TestCase $testCase): RefreshTokenUseCase
    {
        $stub = $testCase->createStub(RefreshTokenUseCase::class);
        
        // Create a mock response
        $mockResponse = self::createMockRefreshTokenResponse($testCase);
        
        $stub->method('execute')
             ->willReturn($mockResponse);
        
        return $stub;
    }
    
    /**
     * Create a mock RefreshTokenUseCase that throws an exception
     */
    public static function createRefreshTokenUseCaseExceptionStub(TestCase $testCase, \Exception $exception): RefreshTokenUseCase
    {
        $stub = $testCase->createStub(RefreshTokenUseCase::class);
        
        $stub->method('execute')
             ->willThrowException($exception);
        
        return $stub;
    }
    
    /**
     * Create a mock RegisterResponse
     */
    private static function createMockRegisterResponse(TestCase $testCase): RegisterResponse
    {
        $userId = UserId::fromString('550e8400-e29b-41d4-a716-446655440001');
        $email = Email::fromString('new@example.com');
        $status = UserStatus::ACTIVE;
        
        $mockUser = $testCase->createMock(User::class);
        $mockUser->method('getId')->willReturn($userId);
        $mockUser->method('getEmail')->willReturn($email);
        $mockUser->method('getFirstName')->willReturn('John');
        $mockUser->method('getLastName')->willReturn('Doe');
        $mockUser->method('getRoles')->willReturn(['ROLE_USER']);
        $mockUser->method('getStatus')->willReturn($status);
        
        $mockResponse = $testCase->createMock(RegisterResponse::class);
        $mockResponse->method('getAccessToken')->willReturn('mock-access-token');
        $mockResponse->method('getRefreshToken')->willReturn('mock-refresh-token');
        $mockResponse->method('getExpiresIn')->willReturn(3600);
        $mockResponse->method('getUser')->willReturn($mockUser);
        
        return $mockResponse;
    }
    
    /**
     * Create a mock RefreshTokenResponse
     */
    private static function createMockRefreshTokenResponse(TestCase $testCase): RefreshTokenResponse
    {
        $mockResponse = $testCase->createMock(RefreshTokenResponse::class);
        $mockResponse->method('getAccessToken')->willReturn('new-access-token');
        $mockResponse->method('getRefreshToken')->willReturn('new-refresh-token');
        $mockResponse->method('getExpiresIn')->willReturn(3600);
        
        return $mockResponse;
    }
}