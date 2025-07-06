<?php

declare(strict_types=1);

namespace App\Application\User\DTO;

use App\Domain\User\Entity\User;

final class LoginResponse
{
    public function __construct(
        private readonly string $accessToken,
        private readonly string $refreshToken,
        private readonly int $expiresIn,
        private readonly User $user
    ) {
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

    public function getUser(): User
    {
        return $this->user;
    }
}