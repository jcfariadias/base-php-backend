<?php

declare(strict_types=1);

namespace App\Application\User\DTO;

final class RegisterCommand
{
    public function __construct(
        private readonly string $email,
        private readonly string $password,
        private readonly string $firstName,
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