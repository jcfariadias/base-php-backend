<?php

declare(strict_types=1);

namespace App\Interfaces\Http\User\Controller;

use App\Application\User\DTO\RefreshTokenCommand;
use App\Application\User\UseCase\RefreshTokenUseCase;
use App\Interfaces\Http\User\Response\AuthResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RefreshTokenController extends AbstractController
{
    public function __construct(
        private readonly RefreshTokenUseCase $refreshTokenUseCase,
        private readonly ValidatorInterface $validator,
        private readonly SerializerInterface $serializer
    ) {}

    #[Route('/api/auth/refresh', name: 'user_refresh_token', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data || !isset($data['refreshToken']) || empty($data['refreshToken'])) {
            return new JsonResponse(['errors' => ['refreshToken: Refresh token is required']], 400);
        }

        try {
            $command = new RefreshTokenCommand($data['refreshToken']);

            $errors = $this->validator->validate($command);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $violation) {
                    $errorMessages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
                }
                return new JsonResponse(['errors' => $errorMessages], 400);
            }

            $response = $this->refreshTokenUseCase->execute($command);

            return new JsonResponse(
                AuthResponse::fromRefreshResponse($response),
                200
            );
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Invalid refresh token'], 401);
        }
    }
}