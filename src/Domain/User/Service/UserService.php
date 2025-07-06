<?php

declare(strict_types=1);

namespace App\Domain\User\Service;

use App\Domain\User\Entity\User;
use App\Domain\User\Event\UserCreated;
use App\Domain\User\Event\UserStatusChanged;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserId;
use App\Domain\User\ValueObject\UserRole;
use App\Domain\User\ValueObject\UserStatus;
use InvalidArgumentException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher
    ) {
    }

    public function createUser(
        Email $email,
        string $plainPassword,
        array $roles = [],
        ?string $tenantId = null,
        UserStatus $status = UserStatus::PENDING
    ): User {
        if ($this->userRepository->existsByEmail($email)) {
            throw new InvalidArgumentException('User with this email already exists');
        }

        $this->validatePassword($plainPassword);

        $userId = UserId::generate();
        $hashedPassword = $this->hashPassword($plainPassword);

        $user = User::create(
            $userId,
            $email,
            $hashedPassword,
            $roles,
            $status,
            $tenantId
        );

        $this->userRepository->save($user);

        // Dispatch domain event
        $event = UserCreated::create(
            $userId,
            $email,
            $status,
            $user->getUserRoles(),
            $tenantId
        );

        return $user;
    }

    public function changeUserStatus(User $user, UserStatus $newStatus): void
    {
        $previousStatus = $user->getStatus();
        
        if ($previousStatus->equals($newStatus)) {
            return;
        }

        $user->changeStatus($newStatus);
        $this->userRepository->save($user);

        // Dispatch domain event
        $event = UserStatusChanged::create(
            $user->getId(),
            $previousStatus,
            $newStatus
        );
    }

    public function activateUser(User $user): void
    {
        $user->activate();
        $this->userRepository->save($user);

        // Dispatch domain event if status actually changed
        if (!$user->getStatus()->equals(UserStatus::ACTIVE)) {
            $event = UserStatusChanged::create(
                $user->getId(),
                $user->getStatus(),
                UserStatus::ACTIVE
            );
        }
    }

    public function deactivateUser(User $user): void
    {
        $user->deactivate();
        $this->userRepository->save($user);

        // Dispatch domain event if status actually changed
        if (!$user->getStatus()->equals(UserStatus::INACTIVE)) {
            $event = UserStatusChanged::create(
                $user->getId(),
                $user->getStatus(),
                UserStatus::INACTIVE
            );
        }
    }

    public function suspendUser(User $user): void
    {
        $previousStatus = $user->getStatus();
        $user->suspend();
        $this->userRepository->save($user);

        // Dispatch domain event
        $event = UserStatusChanged::create(
            $user->getId(),
            $previousStatus,
            UserStatus::SUSPENDED
        );
    }

    public function deleteUser(User $user): void
    {
        $previousStatus = $user->getStatus();
        $user->markAsDeleted();
        $this->userRepository->save($user);

        // Dispatch domain event
        $event = UserStatusChanged::create(
            $user->getId(),
            $previousStatus,
            UserStatus::DELETED
        );
    }

    public function changeUserPassword(User $user, string $newPlainPassword): void
    {
        $this->validatePassword($newPlainPassword);
        $hashedPassword = $this->hashPassword($newPlainPassword);
        
        $user->changePassword($hashedPassword);
        $this->userRepository->save($user);
    }

    public function changeUserEmail(User $user, Email $newEmail): void
    {
        if ($this->userRepository->existsByEmail($newEmail)) {
            throw new InvalidArgumentException('User with this email already exists');
        }

        $user->changeEmail($newEmail);
        $this->userRepository->save($user);
    }

    public function assignUserToTenant(User $user, string $tenantId): void
    {
        if (empty($tenantId)) {
            throw new InvalidArgumentException('Tenant ID cannot be empty');
        }

        $user->assignToTenant($tenantId);
        $this->userRepository->save($user);
    }

    public function removeUserFromTenant(User $user): void
    {
        $user->removeFromTenant();
        $this->userRepository->save($user);
    }

    public function addRoleToUser(User $user, UserRole $role): void
    {
        $user->addRole($role);
        $this->userRepository->save($user);
    }

    public function removeRoleFromUser(User $user, UserRole $role): void
    {
        $user->removeRole($role);
        $this->userRepository->save($user);
    }

    public function verifyPassword(User $user, string $plainPassword): bool
    {
        return $this->passwordHasher->verify($user->getPassword(), $plainPassword);
    }

    public function isPasswordValid(string $password): bool
    {
        try {
            $this->validatePassword($password);
            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public function canUserAccessTenant(User $user, string $tenantId): bool
    {
        // Super admin can access any tenant
        if ($user->hasRole(UserRole::ADMIN)) {
            return true;
        }

        // Check if user belongs to the tenant
        return $user->belongsToTenant($tenantId);
    }

    public function canUserManageUser(User $manager, User $targetUser): bool
    {
        // Admin can manage anyone
        if ($manager->hasRole(UserRole::ADMIN)) {
            return true;
        }

        // Tenant admin can manage users in their tenant
        if ($manager->hasRole(UserRole::TENANT_ADMIN) && 
            $manager->getTenantId() && 
            $targetUser->belongsToTenant($manager->getTenantId())) {
            return true;
        }

        // Manager can manage regular users in their tenant
        if ($manager->hasRole(UserRole::MANAGER) && 
            $manager->getTenantId() && 
            $targetUser->belongsToTenant($manager->getTenantId()) &&
            !$targetUser->hasManagerPrivileges()) {
            return true;
        }

        return false;
    }

    private function validatePassword(string $password): void
    {
        if (empty($password)) {
            throw new InvalidArgumentException('Password cannot be empty');
        }

        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long');
        }

        if (strlen($password) > 128) {
            throw new InvalidArgumentException('Password cannot be longer than 128 characters');
        }

        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            throw new InvalidArgumentException('Password must contain at least one uppercase letter');
        }

        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            throw new InvalidArgumentException('Password must contain at least one lowercase letter');
        }

        // Check for at least one digit
        if (!preg_match('/\d/', $password)) {
            throw new InvalidArgumentException('Password must contain at least one digit');
        }

        // Check for at least one special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            throw new InvalidArgumentException('Password must contain at least one special character');
        }
    }

    private function hashPassword(string $plainPassword): string
    {
        return $this->passwordHasher->hash($plainPassword);
    }
}