<?php

declare(strict_types=1);

namespace App\Interfaces\Http\User\Response;

use App\Application\User\DTO\LoginResponse;
use App\Application\User\DTO\RefreshTokenResponse;
use App\Application\User\DTO\RegisterResponse;

final class AuthResponse
{
    public function __construct(
        private readonly string $accessToken,
        private readonly string $refreshToken,
        private readonly int $expiresIn,
        private readonly string $tokenType = 'Bearer',
        private readonly ?UserResponse $user = null
    ) {
    }

    public static function fromLoginResponse(LoginResponse $response): array
    {
        return [
            'access_token' => $response->getAccessToken(),
            'refresh_token' => $response->getRefreshToken(),
            'token_type' => 'Bearer',
            'expires_in' => $response->getExpiresIn(),
            'user' => $response->getUser() ? UserResponse::fromUser($response->getUser())->toArray() : null
        ];
    }

    public static function fromRefreshResponse(RefreshTokenResponse $response): array
    {
        return [
            'access_token' => $response->getAccessToken(),
            'refresh_token' => $response->getRefreshToken(),
            'token_type' => 'Bearer',
            'expires_in' => $response->getExpiresIn()
        ];
    }

    public static function fromRegisterResponse(RegisterResponse $response): array
    {
        return [
            'access_token' => $response->getAccessToken(),
            'refresh_token' => $response->getRefreshToken(),
            'token_type' => 'Bearer',
            'expires_in' => $response->getExpiresIn(),
            'user' => UserResponse::fromUser($response->getUser())->toArray(),
            'message' => 'User registered successfully'
        ];
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    public function getUser(): ?UserResponse
    {
        return $this->user;
    }

    public function toArray(): array
    {
        return [
            'access_token' => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
            'user' => $this->user?->toArray()
        ];
    }
}