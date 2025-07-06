<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Service;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;

final class JwtTokenValidator
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtTokenManager
    ) {
    }

    public function validateAccessToken(string $token): bool
    {
        try {
            $payload = $this->jwtTokenManager->parse($token);
            
            // Check if token is not a refresh token
            if (isset($payload['type']) && $payload['type'] === 'refresh') {
                return false;
            }

            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public function validateRefreshToken(string $token): bool
    {
        try {
            $payload = $this->jwtTokenManager->parse($token);
            
            // Check if token is specifically a refresh token
            if (!isset($payload['type']) || $payload['type'] !== 'refresh') {
                return false;
            }

            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public function getPayload(string $token): array
    {
        try {
            return $this->jwtTokenManager->parse($token);
        } catch (\Exception) {
            throw new \InvalidArgumentException('Invalid token format');
        }
    }
}