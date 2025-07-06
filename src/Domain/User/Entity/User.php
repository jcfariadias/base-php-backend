<?php

declare(strict_types=1);

namespace App\Domain\User\Entity;

use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserId;
use App\Domain\User\ValueObject\UserRole;
use App\Domain\User\ValueObject\UserStatus;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[ORM\Index(columns: ['email'], name: 'idx_user_email')]
#[ORM\Index(columns: ['status'], name: 'idx_user_status')]
#[ORM\Index(columns: ['tenant_id'], name: 'idx_user_tenant')]
#[ORM\UniqueConstraint(name: 'uniq_user_email', columns: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private readonly string $id;

    #[ORM\Column(type: Types::STRING, length: 254, unique: true)]
    private string $email;

    #[ORM\Column(type: Types::TEXT)]
    private string $password;

    #[ORM\Column(type: Types::JSON)]
    private array $roles;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: UserStatus::class)]
    private UserStatus $status;

    #[ORM\Column(name: 'tenant_id', type: 'uuid', nullable: true)]
    private ?string $tenantId = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $updatedAt;

    private function __construct(
        UserId $id,
        Email $email,
        string $hashedPassword,
        array $roles,
        UserStatus $status,
        ?string $tenantId = null
    ) {
        $this->id = $id->toString();
        $this->email = $email->toString();
        $this->password = $hashedPassword;
        $this->setRoles($roles);
        $this->status = $status;
        $this->tenantId = $tenantId;
        
        $now = new DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public static function create(
        UserId $id,
        Email $email,
        string $hashedPassword,
        array $roles = [],
        UserStatus $status = UserStatus::PENDING,
        ?string $tenantId = null
    ): self {
        if (empty($hashedPassword)) {
            throw new InvalidArgumentException('Password cannot be empty');
        }

        if (empty($roles)) {
            $roles = [UserRole::USER];
        }

        return new self($id, $email, $hashedPassword, $roles, $status, $tenantId);
    }

    public function getId(): UserId
    {
        return UserId::fromString($this->id);
    }

    public function getEmail(): Email
    {
        return Email::fromString($this->email);
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        return array_map(fn(UserRole $role) => $role->toString(), $this->roles);
    }

    public function getUserRoles(): array
    {
        return $this->roles;
    }

    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function changeEmail(Email $email): void
    {
        if ($this->email === $email->toString()) {
            return;
        }

        $this->email = $email->toString();
        $this->touch();
    }

    public function changePassword(string $hashedPassword): void
    {
        if (empty($hashedPassword)) {
            throw new InvalidArgumentException('Password cannot be empty');
        }

        $this->password = $hashedPassword;
        $this->touch();
    }

    public function changeStatus(UserStatus $status): void
    {
        if ($this->status === $status) {
            return;
        }

        $this->status = $status;
        $this->touch();
    }

    public function activate(): void
    {
        if (!$this->status->canBeActivated()) {
            throw new InvalidArgumentException(
                sprintf('User with status "%s" cannot be activated', $this->status->toString())
            );
        }

        $this->changeStatus(UserStatus::ACTIVE);
    }

    public function deactivate(): void
    {
        if (!$this->status->canBeDeactivated()) {
            throw new InvalidArgumentException(
                sprintf('User with status "%s" cannot be deactivated', $this->status->toString())
            );
        }

        $this->changeStatus(UserStatus::INACTIVE);
    }

    public function suspend(): void
    {
        if ($this->status === UserStatus::SUSPENDED) {
            return;
        }

        $this->changeStatus(UserStatus::SUSPENDED);
    }

    public function markAsDeleted(): void
    {
        $this->changeStatus(UserStatus::DELETED);
    }

    public function addRole(UserRole $role): void
    {
        if ($this->hasRole($role)) {
            return;
        }

        $this->roles[] = $role;
        $this->touch();
    }

    public function removeRole(UserRole $role): void
    {
        $this->roles = array_filter(
            $this->roles,
            fn(UserRole $existingRole) => !$existingRole->equals($role)
        );
        $this->roles = array_values($this->roles); // Re-index array
        $this->touch();
    }

    public function hasRole(UserRole $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function assignToTenant(string $tenantId): void
    {
        if (empty($tenantId)) {
            throw new InvalidArgumentException('Tenant ID cannot be empty');
        }

        $this->tenantId = $tenantId;
        $this->touch();
    }

    public function removeFromTenant(): void
    {
        $this->tenantId = null;
        $this->touch();
    }

    public function belongsToTenant(string $tenantId): bool
    {
        return $this->tenantId === $tenantId;
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function canLogin(): bool
    {
        return $this->status->canLogin();
    }

    public function isDeleted(): bool
    {
        return $this->status->isDeleted();
    }

    public function isSuspended(): bool
    {
        return $this->status->isSuspended();
    }

    public function hasAdminPrivileges(): bool
    {
        foreach ($this->roles as $role) {
            if ($role->hasAdminPrivileges()) {
                return true;
            }
        }

        return false;
    }

    public function hasManagerPrivileges(): bool
    {
        foreach ($this->roles as $role) {
            if ($role->hasManagerPrivileges()) {
                return true;
            }
        }

        return false;
    }

    public function canManageTenant(): bool
    {
        foreach ($this->roles as $role) {
            if ($role->canManageTenant()) {
                return true;
            }
        }

        return false;
    }

    public function eraseCredentials(): void
    {
        // This method is intentionally left blank as we don't store plain passwords
    }

    private function setRoles(array $roles): void
    {
        $userRoles = [];
        foreach ($roles as $role) {
            if ($role instanceof UserRole) {
                $userRoles[] = $role;
            } elseif (is_string($role)) {
                $userRoles[] = UserRole::fromString($role);
            } else {
                throw new InvalidArgumentException('Invalid role type');
            }
        }

        // Ensure we always have at least one role
        if (empty($userRoles)) {
            $userRoles[] = UserRole::USER;
        }

        // Remove duplicates
        $uniqueRoles = [];
        foreach ($userRoles as $role) {
            $found = false;
            foreach ($uniqueRoles as $uniqueRole) {
                if ($role->equals($uniqueRole)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $uniqueRoles[] = $role;
            }
        }

        $this->roles = $uniqueRoles;
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->email;
    }
}