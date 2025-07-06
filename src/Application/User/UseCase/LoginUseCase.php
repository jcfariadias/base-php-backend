<?php

declare(strict_types=1);

namespace App\Application\User\UseCase;

use App\Application\User\DTO\LoginCommand;
use App\Application\User\DTO\LoginResponse;
use App\Application\User\Service\AuthenticationService;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class LoginUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly AuthenticationService $authenticationService
    ) {
    }

    public function execute(LoginCommand $command): LoginResponse
    {
        $email = Email::fromString($command->getEmail());
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new \InvalidArgumentException('Invalid credentials');
        }

        if (!$user->isActive()) {
            throw new \InvalidArgumentException('User account is not active');
        }

        if (!$this->passwordHasher->isPasswordValid($user, $command->getPassword())) {
            throw new \InvalidArgumentException('Invalid credentials');
        }

        $tokenPair = $this->authenticationService->generateTokens($user);

        return new LoginResponse(
            $tokenPair->getAccessToken(),
            $tokenPair->getRefreshToken(),
            $tokenPair->getExpiresIn(),
            $user
        );
    }
}