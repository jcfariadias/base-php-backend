<?php

declare(strict_types=1);

namespace App\Interfaces\Http\User\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

final class RegisterRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Please provide a valid email address')]
        private readonly string $email,

        #[Assert\NotBlank(message: 'Password is required')]
        #[Assert\Length(min: 8, minMessage: 'Password must be at least 8 characters long')]
        #[Assert\Regex(
            pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
            message: 'Password must contain at least one uppercase letter, one lowercase letter, and one number'
        )]
        private readonly string $password,

        #[Assert\NotBlank(message: 'First name is required')]
        #[Assert\Length(min: 2, max: 50, minMessage: 'First name must be at least 2 characters long', maxMessage: 'First name cannot exceed 50 characters')]
        private readonly string $firstName,

        #[Assert\NotBlank(message: 'Last name is required')]
        #[Assert\Length(min: 2, max: 50, minMessage: 'Last name must be at least 2 characters long', maxMessage: 'Last name cannot exceed 50 characters')]
        private readonly string $lastName,

        private readonly ?string $tenantId = null
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
        $firstName = $data['first_name'] ?? '';
        $lastName = $data['last_name'] ?? '';
        $tenantId = $data['tenant_id'] ?? null;

        $instance = new self($email, $password, $firstName, $lastName, $tenantId);
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

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
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