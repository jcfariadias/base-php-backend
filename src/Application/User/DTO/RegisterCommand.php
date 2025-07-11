<?php

declare(strict_types=1);

namespace App\Application\User\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class RegisterCommand
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Please provide a valid email address')]
        private readonly string $email,
        
        #[Assert\NotBlank(message: 'Password is required')]
        #[Assert\Length(min: 6, minMessage: 'Password must be at least 6 characters long')]
        private readonly string $password,
        
        #[Assert\NotBlank(message: 'First name is required')]
        private readonly string $firstName,
        
        #[Assert\NotBlank(message: 'Last name is required')]
        private readonly string $lastName,
        
        private readonly ?string $tenantId = null
    ) {
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
}