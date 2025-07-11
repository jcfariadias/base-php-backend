<?php

declare(strict_types=1);

namespace App\Application\User\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class RefreshTokenCommand
{
    public function __construct(
        #[Assert\NotBlank(message: 'Refresh token is required')]
        private readonly string $refreshToken
    ) {
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}