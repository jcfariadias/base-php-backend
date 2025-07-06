<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use InvalidArgumentException;

enum UserRole: string
{
    case ADMIN = 'ROLE_ADMIN';
    case USER = 'ROLE_USER';
    case MANAGER = 'ROLE_MANAGER';
    case TENANT_ADMIN = 'ROLE_TENANT_ADMIN';
    case TENANT_USER = 'ROLE_TENANT_USER';

    public static function fromString(string $value): self
    {
        $role = self::tryFrom($value);
        
        if ($role === null) {
            throw new InvalidArgumentException(
                sprintf('Invalid user role: %s. Valid roles are: %s', 
                    $value, 
                    implode(', ', array_map(fn($case) => $case->value, self::cases()))
                )
            );
        }

        return $role;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(UserRole $other): bool
    {
        return $this->value === $other->value;
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function isUser(): bool
    {
        return $this === self::USER;
    }

    public function isManager(): bool
    {
        return $this === self::MANAGER;
    }

    public function isTenantAdmin(): bool
    {
        return $this === self::TENANT_ADMIN;
    }

    public function isTenantUser(): bool
    {
        return $this === self::TENANT_USER;
    }

    public function hasAdminPrivileges(): bool
    {
        return in_array($this, [self::ADMIN, self::TENANT_ADMIN], true);
    }

    public function hasManagerPrivileges(): bool
    {
        return in_array($this, [self::ADMIN, self::MANAGER, self::TENANT_ADMIN], true);
    }

    public function canManageTenant(): bool
    {
        return in_array($this, [self::ADMIN, self::TENANT_ADMIN], true);
    }

    public function getHierarchyLevel(): int
    {
        return match ($this) {
            self::ADMIN => 5,
            self::TENANT_ADMIN => 4,
            self::MANAGER => 3,
            self::TENANT_USER => 2,
            self::USER => 1,
        };
    }

    public function canAccessRole(UserRole $targetRole): bool
    {
        return $this->getHierarchyLevel() >= $targetRole->getHierarchyLevel();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}