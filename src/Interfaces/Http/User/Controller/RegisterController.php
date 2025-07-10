<?php

declare(strict_types=1);

namespace App\Interfaces\Http\User\Controller;

use App\Application\User\DTO\RegisterCommand;
use App\Application\User\UseCase\RegisterUseCase;
use App\Interfaces\Http\User\Response\AuthResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterController extends AbstractController
{
    public function __construct(
        private readonly RegisterUseCase $registerUseCase,
        private readonly ValidatorInterface $validator,
        private readonly SerializerInterface $serializer
    ) {}

    #[Route('/api/auth/register', name: 'user_register', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $command = $this->serializer->deserialize(
                $request->getContent(),
                RegisterCommand::class,
                'json'
            );

            $errors = $this->validator->validate($command);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $violation) {
                    $errorMessages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
                }
                return new JsonResponse(['errors' => $errorMessages], 400);
            }

            $response = $this->registerUseCase->execute($command);

            return new JsonResponse(
                AuthResponse::fromLoginResponse($response),
                201
            );
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Registration failed'], 400);
        }
    }
}