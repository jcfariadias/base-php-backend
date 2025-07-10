<?php

declare(strict_types=1);

namespace App\Interfaces\Http\User\Controller;

use App\Domain\User\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class GetCurrentUserController extends AbstractController
{
    #[Route('/api/auth/me', name: 'user_current', methods: ['GET'])]
    public function __invoke(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(
                ['error' => 'Not authenticated'],
                401
            );
        }

        return new JsonResponse([
            'id' => $user->getId()->toString(),
            'email' => $user->getEmail()->toString(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'roles' => $user->getRoles(),
            'status' => $user->getStatus()->toString(),
        ], 200);
    }
}