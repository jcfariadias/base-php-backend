<?php

declare(strict_types=1);

namespace App\Interfaces\Http\User\Controller;

use App\Application\User\DTO\LoginCommand;
use App\Application\User\DTO\RefreshTokenCommand;
use App\Application\User\DTO\RegisterCommand;
use App\Application\User\UseCase\LoginUseCase;
use App\Application\User\UseCase\RefreshTokenUseCase;
use App\Application\User\UseCase\RegisterUseCase;
use App\Interfaces\Http\User\Request\LoginRequest;
use App\Interfaces\Http\User\Request\RefreshTokenRequest;
use App\Interfaces\Http\User\Request\RegisterRequest;
use App\Interfaces\Http\User\Response\AuthResponse;
use App\Interfaces\Http\User\Response\UserResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Domain\User\Entity\User;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly LoginUseCase $loginUseCase,
        private readonly RefreshTokenUseCase $refreshTokenUseCase,
        private readonly RegisterUseCase $registerUseCase
    ) {
    }

    #[Route('/login', name: 'auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $loginRequest = LoginRequest::fromRequest($request);
            
            $command = new LoginCommand(
                $loginRequest->getEmail(),
                $loginRequest->getPassword(),
                $loginRequest->getRememberMe() ?? false
            );

            $loginResponse = $this->loginUseCase->execute($command);

            return $this->json(
                AuthResponse::fromLoginResponse($loginResponse),
                Response::HTTP_OK
            );
        } catch (\InvalidArgumentException $e) {
            return $this->json(
                ['error' => 'Invalid credentials', 'message' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'Authentication failed', 'message' => 'Invalid credentials'],
                Response::HTTP_UNAUTHORIZED
            );
        }
    }

    #[Route('/logout', name: 'auth_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        // JWT tokens are stateless, so logout is handled on the client side
        // In the future, we could implement token blacklisting here
        return $this->json([
            'message' => 'Successfully logged out',
            'status' => 'success'
        ], Response::HTTP_OK);
    }

    #[Route('/refresh', name: 'auth_refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        try {
            $refreshRequest = RefreshTokenRequest::fromRequest($request);
            
            $command = new RefreshTokenCommand(
                $refreshRequest->getRefreshToken()
            );

            $refreshResponse = $this->refreshTokenUseCase->execute($command);

            return $this->json(
                AuthResponse::fromRefreshResponse($refreshResponse),
                Response::HTTP_OK
            );
        } catch (\InvalidArgumentException $e) {
            return $this->json(
                ['error' => 'Invalid refresh token', 'message' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'Token refresh failed', 'message' => 'Invalid or expired refresh token'],
                Response::HTTP_UNAUTHORIZED
            );
        }
    }

    #[Route('/register', name: 'auth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            $registerRequest = RegisterRequest::fromRequest($request);
            
            $command = new RegisterCommand(
                $registerRequest->getEmail(),
                $registerRequest->getPassword(),
                $registerRequest->getFirstName(),
                $registerRequest->getLastName(),
                $registerRequest->getTenantId()
            );

            $registerResponse = $this->registerUseCase->execute($command);

            return $this->json(
                AuthResponse::fromRegisterResponse($registerResponse),
                Response::HTTP_CREATED
            );
        } catch (\InvalidArgumentException $e) {
            return $this->json(
                ['error' => 'Validation failed', 'message' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'Registration failed', 'message' => $e->getMessage()],
                Response::HTTP_CONFLICT
            );
        }
    }

    #[Route('/me', name: 'auth_me', methods: ['GET'])]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(
                ['error' => 'Not authenticated'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        return $this->json(
            UserResponse::fromUser($user),
            Response::HTTP_OK
        );
    }
}