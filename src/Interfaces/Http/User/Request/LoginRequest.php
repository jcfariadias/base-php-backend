<?php

declare(strict_types=1);

namespace App\Interfaces\Http\User\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

final class LoginRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Please provide a valid email address')]
        private readonly string $email,

        #[Assert\NotBlank(message: 'Password is required')]
        #[Assert\Length(min: 6, minMessage: 'Password must be at least 6 characters long')]
        private readonly string $password,

        private readonly ?bool $rememberMe = false
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $data = json_decode($request->getContent(), true);
        
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Invalid JSON payload');
        }

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $rememberMe = $data['remember_me'] ?? false;

        $instance = new self($email, $password, $rememberMe);
        $instance->validate();

        return $instance;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRememberMe(): ?bool
    {
        return $this->rememberMe;
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