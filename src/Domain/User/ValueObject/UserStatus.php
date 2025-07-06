<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use InvalidArgumentException;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
    case SUSPENDED = 'suspended';
    case DELETED = 'deleted';

    public static function fromString(string $value): self
    {
        $status = self::tryFrom($value);
        
        if ($status === null) {
            throw new InvalidArgumentException(
                sprintf('Invalid user status: %s. Valid statuses are: %s', 
                    $value, 
                    implode(', ', array_map(fn($case) => $case->value, self::cases()))
                )
            );
        }

        return $status;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(UserStatus $other): bool
    {
        return $this->value === $other->value;
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this === self::INACTIVE;
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isSuspended(): bool
    {
        return $this === self::SUSPENDED;
    }

    public function isDeleted(): bool
    {
        return $this === self::DELETED;
    }

    public function canLogin(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canBeActivated(): bool
    {
        return in_array($this, [self::INACTIVE, self::PENDING, self::SUSPENDED], true);
    }

    public function canBeDeactivated(): bool
    {
        return $this === self::ACTIVE;
    }
}