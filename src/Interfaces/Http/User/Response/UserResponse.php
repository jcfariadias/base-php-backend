<?php

declare(strict_types=1);

namespace App\Interfaces\Http\User\Response;

use App\Domain\User\Entity\User;

final class UserResponse
{
    public function __construct(
        private readonly string $id,
        private readonly string $email,
        private readonly string $firstName,
        private readonly string $lastName,
        private readonly array $roles,
        private readonly string $status,
        private readonly ?string $tenantId,
        private readonly string $createdAt,
        private readonly string $updatedAt
    ) {
    }

    public static function fromUser(User $user): self
    {
        return new self(
            $user->getId()->toString(),
            $user->getEmail()->toString(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getRoles(),
            $user->getStatus()->toString(),
            $user->getTenantId(),
            $user->getCreatedAt()->format('Y-m-d\TH:i:s.u\Z'),
            $user->getUpdatedAt()->format('Y-m-d\TH:i:s.u\Z')
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
    
    public function getFirstName(): string
    {
        return $this->firstName;
    }
    
    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'roles' => $this->roles,
            'status' => $this->status,
            'tenant_id' => $this->tenantId,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}