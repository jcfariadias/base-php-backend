<?php

declare(strict_types=1);

namespace App\Interfaces\Http\User\Controller;

use App\Application\User\DTO\LoginCommand;
use App\Application\User\UseCase\LoginUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginController extends AbstractController
{
    public function __construct(
        private readonly LoginUseCase $loginUseCase,
        private readonly ValidatorInterface $validator,
        private readonly SerializerInterface $serializer
    ) {}

    #[Route('/api/auth/login', name: 'user_login', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        /** @var LoginCommand $command */
        $command = $this->serializer->deserialize(
            $request->getContent(),
            LoginCommand::class,
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

        $response = $this->loginUseCase->execute($command);

        return new JsonResponse([
            'access_token' => $response->getAccessToken(),
            'refresh_token' => $response->getRefreshToken(),
            'token_type' => 'Bearer',
            'expires_in' => $response->getExpiresIn(),
            'user' => [
                'id' => $response->getUser()->getId()->toString(),
                'email' => $response->getUser()->getEmail()->toString(),
                'roles' => $response->getUser()->getRoles(),
                'status' => $response->getUser()->getStatus()->toString(),
            ]
        ], 200);
    }
}