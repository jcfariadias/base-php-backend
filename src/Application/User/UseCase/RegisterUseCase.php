<?php

declare(strict_types=1);

namespace App\Application\User\UseCase;

use App\Application\User\DTO\RegisterCommand;
use App\Application\User\DTO\RegisterResponse;
use App\Application\User\Service\AuthenticationService;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserId;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegisterUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly AuthenticationService $authenticationService
    ) {
    }

    public function execute(RegisterCommand $command): RegisterResponse
    {
        $email = Email::fromString($command->getEmail());
        
        // Check if user already exists
        $existingUser = $this->userRepository->findByEmail($email);
        if ($existingUser) {
            throw new \InvalidArgumentException('User with this email already exists');
        }

        // Create new user
        $userId = UserId::generate();
        $user = new User(
            $userId,
            $email,
            '', // Will be set below
            ['ROLE_USER'],
            $command->getTenantId()
        );

        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $command->getPassword());
        $user->setPassword($hashedPassword);

        // Save user
        $this->userRepository->save($user);

        // Generate tokens
        $tokenPair = $this->authenticationService->generateTokens($user);

        return new RegisterResponse(
            $tokenPair->getAccessToken(),
            $tokenPair->getRefreshToken(),
            $tokenPair->getExpiresIn(),
            $user
        );
    }
}