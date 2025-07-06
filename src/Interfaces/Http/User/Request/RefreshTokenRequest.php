<?php

declare(strict_types=1);

namespace App\Interfaces\Http\User\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

final class RefreshTokenRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Refresh token is required')]
        private readonly string $refreshToken
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $data = json_decode($request->getContent(), true);
        
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Invalid JSON payload');
        }

        $refreshToken = $data['refresh_token'] ?? '';

        $instance = new self($refreshToken);
        $instance->validate();

        return $instance;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    private function validate(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $violations = $validator->validate($this);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            throw new \InvalidArgumentException(implode(', ', $errors));
        }
    }
}