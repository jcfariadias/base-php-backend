<?php

declare(strict_types=1);

namespace App\Infrastructure\User\EventListener;

use App\Domain\User\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

final class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();
        
        if (!$user instanceof User) {
            return;
        }

        $payload = $event->getData();
        
        // Ensure the email is properly set as a string
        $payload['email'] = $user->getEmail()->toString();
        
        // Add user ID for better token identification
        $payload['user_id'] = $user->getId()->toString();
        
        $event->setData($payload);
    }
}