<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Service;

use App\Domain\User\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class JwtTokenGenerator
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtTokenManager
    ) {
    }

    public function generateAccessToken(User $user): string
    {
        return $this->jwtTokenManager->create($user);
    }

    public function generateRefreshToken(User $user): string
    {
        // For simplicity, using the same JWT structure for refresh tokens
        // In production, you might want separate refresh token logic
        $payload = [
            'sub' => $user->getUserIdentifier(),
            'type' => 'refresh',
            'exp' => time() + (7 * 24 * 3600), // 7 days
            'iat' => time(),
        ];

        return $this->jwtTokenManager->createFromPayload($user, $payload);
    }
}