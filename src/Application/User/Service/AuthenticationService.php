<?php

declare(strict_types=1);

namespace App\Application\User\Service;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\TokenPair;
use App\Infrastructure\User\Service\JwtTokenGenerator;
use App\Infrastructure\User\Service\JwtTokenValidator;

final class AuthenticationService
{
    public function __construct(
        private readonly JwtTokenGenerator $tokenGenerator,
        private readonly JwtTokenValidator $tokenValidator,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function generateTokens(User $user): TokenPair
    {
        $accessToken = $this->tokenGenerator->generateAccessToken($user);
        $refreshToken = $this->tokenGenerator->generateRefreshToken($user);
        
        return new TokenPair(
            $accessToken,
            $refreshToken,
            3600 // 1 hour
        );
    }

    public function refreshTokens(string $refreshToken): TokenPair
    {
        // Validate refresh token
        if (!$this->tokenValidator->validateRefreshToken($refreshToken)) {
            throw new \InvalidArgumentException('Invalid or expired refresh token');
        }

        // Get user from refresh token
        $payload = $this->tokenValidator->getPayload($refreshToken);
        $userEmail = $payload['sub'] ?? null;
        
        if (!$userEmail) {
            throw new \InvalidArgumentException('Invalid token payload');
        }

        $user = $this->userRepository->findByEmail(
            \App\Domain\User\ValueObject\Email::fromString($userEmail)
        );

        if (!$user || !$user->isActive()) {
            throw new \InvalidArgumentException('User not found or inactive');
        }

        // Generate new token pair
        return $this->generateTokens($user);
    }

    public function validateAccessToken(string $accessToken): bool
    {
        return $this->tokenValidator->validateAccessToken($accessToken);
    }

    public function getUserFromToken(string $accessToken): ?User
    {
        if (!$this->validateAccessToken($accessToken)) {
            return null;
        }

        $payload = $this->tokenValidator->getPayload($accessToken);
        $userEmail = $payload['sub'] ?? null;
        
        if (!$userEmail) {
            return null;
        }

        return $this->userRepository->findByEmail(
            \App\Domain\User\ValueObject\Email::fromString($userEmail)
        );
    }
}