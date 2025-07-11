<?php

declare(strict_types=1);

namespace App\Application\User\UseCase;

use App\Application\User\DTO\RefreshTokenCommand;
use App\Application\User\DTO\RefreshTokenResponse;
use App\Application\User\Service\AuthenticationService;

class RefreshTokenUseCase
{
    public function __construct(
        private readonly AuthenticationService $authenticationService
    ) {
    }

    public function execute(RefreshTokenCommand $command): RefreshTokenResponse
    {
        $tokenPair = $this->authenticationService->refreshTokens($command->getRefreshToken());

        return new RefreshTokenResponse(
            $tokenPair->getAccessToken(),
            $tokenPair->getRefreshToken(),
            $tokenPair->getExpiresIn()
        );
    }
}