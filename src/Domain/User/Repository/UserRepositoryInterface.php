<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserId;
use App\Domain\User\ValueObject\UserStatus;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function remove(User $user): void;

    public function findById(UserId $id): ?User;

    public function findByEmail(Email $email): ?User;

    public function existsByEmail(Email $email): bool;

    public function findByIds(array $ids): array;

    public function findByTenantId(string $tenantId, int $limit = 100, int $offset = 0): array;

    public function findByStatus(UserStatus $status, int $limit = 100, int $offset = 0): array;

    public function findByTenantIdAndStatus(
        string $tenantId, 
        UserStatus $status, 
        int $limit = 100, 
        int $offset = 0
    ): array;

    public function findActiveUsers(int $limit = 100, int $offset = 0): array;

    public function findUsersWithRole(string $role, int $limit = 100, int $offset = 0): array;

    public function countByTenantId(string $tenantId): int;

    public function countByStatus(UserStatus $status): int;

    public function countByTenantIdAndStatus(string $tenantId, UserStatus $status): int;

    public function findUsersCreatedBetween(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        int $limit = 100,
        int $offset = 0
    ): array;

    public function searchByEmailPattern(string $pattern, int $limit = 100, int $offset = 0): array;

    public function findExpiredPendingUsers(\DateTimeImmutable $expiredBefore): array;

    public function findAll(int $limit = 100, int $offset = 0): array;

    public function count(): int;
}