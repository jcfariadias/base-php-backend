<?php

declare(strict_types=1);

namespace App\Application\User\DTO;

final class RefreshTokenCommand
{
    public function __construct(
        private readonly string $refreshToken
    ) {
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}