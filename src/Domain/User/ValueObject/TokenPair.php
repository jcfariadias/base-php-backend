<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

final class TokenPair
{
    public function __construct(
        private readonly string $accessToken,
        private readonly string $refreshToken,
        private readonly int $expiresIn
    ) {
        if (empty($accessToken)) {
            throw new \InvalidArgumentException('Access token cannot be empty');
        }

        if (empty($refreshToken)) {
            throw new \InvalidArgumentException('Refresh token cannot be empty');
        }

        if ($expiresIn <= 0) {
            throw new \InvalidArgumentException('Expires in must be positive');
        }
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

    public function equals(TokenPair $other): bool
    {
        return $this->accessToken === $other->accessToken
            && $this->refreshToken === $other->refreshToken
            && $this->expiresIn === $other->expiresIn;
    }
}