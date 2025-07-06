<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Domain\User\ValueObject\UserId;
use App\Domain\User\ValueObject\UserStatus;
use DateTimeImmutable;

final class UserStatusChanged
{
    public function __construct(
        private readonly UserId $userId,
        private readonly UserStatus $previousStatus,
        private readonly UserStatus $newStatus,
        private readonly DateTimeImmutable $occurredAt
    ) {
    }

    public static function create(
        UserId $userId,
        UserStatus $previousStatus,
        UserStatus $newStatus
    ): self {
        return new self(
            $userId,
            $previousStatus,
            $newStatus,
            new DateTimeImmutable()
        );
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getPreviousStatus(): UserStatus
    {
        return $this->previousStatus;
    }

    public function getNewStatus(): UserStatus
    {
        return $this->newStatus;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function isActivation(): bool
    {
        return $this->newStatus->isActive() && !$this->previousStatus->isActive();
    }

    public function isDeactivation(): bool
    {
        return !$this->newStatus->isActive() && $this->previousStatus->isActive();
    }

    public function isSuspension(): bool
    {
        return $this->newStatus->isSuspended() && !$this->previousStatus->isSuspended();
    }

    public function isDeletion(): bool
    {
        return $this->newStatus->isDeleted() && !$this->previousStatus->isDeleted();
    }

    public function toArray(): array
    {
        return [
            'userId' => $this->userId->toString(),
            'previousStatus' => $this->previousStatus->toString(),
            'newStatus' => $this->newStatus->toString(),
            'occurredAt' => $this->occurredAt->format('Y-m-d H:i:s'),
            'isActivation' => $this->isActivation(),
            'isDeactivation' => $this->isDeactivation(),
            'isSuspension' => $this->isSuspension(),
            'isDeletion' => $this->isDeletion(),
        ];
    }
}