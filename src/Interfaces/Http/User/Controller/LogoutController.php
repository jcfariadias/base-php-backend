<?php

declare(strict_types=1);

namespace App\Interfaces\Http\User\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class LogoutController extends AbstractController
{
    #[Route('/api/auth/logout', name: 'user_logout', methods: ['POST'])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Successfully logged out',
            'status' => 'success'
        ], 200);
    }
}