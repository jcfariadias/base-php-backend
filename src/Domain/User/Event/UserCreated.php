<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserId;
use App\Domain\User\ValueObject\UserStatus;
use DateTimeImmutable;

final class UserCreated
{
    public function __construct(
        private readonly UserId $userId,
        private readonly Email $email,
        private readonly UserStatus $status,
        private readonly array $roles,
        private readonly ?string $tenantId,
        private readonly DateTimeImmutable $occurredAt
    ) {
    }

    public static function create(
        UserId $userId,
        Email $email,
        UserStatus $status,
        array $roles,
        ?string $tenantId = null
    ): self {
        return new self(
            $userId,
            $email,
            $status,
            $roles,
            $tenantId,
            new DateTimeImmutable()
        );
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function toArray(): array
    {
        return [
            'userId' => $this->userId->toString(),
            'email' => $this->email->toString(),
            'status' => $this->status->toString(),
            'roles' => array_map(fn($role) => $role->toString(), $this->roles),
            'tenantId' => $this->tenantId,
            'occurredAt' => $this->occurredAt->format('Y-m-d H:i:s'),
        ];
    }
}