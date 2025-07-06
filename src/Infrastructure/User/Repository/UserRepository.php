<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Repository;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findById(UserId $id): ?User
    {
        return $this->find($id->toString());
    }

    public function findByEmail(Email $email): ?User
    {
        return $this->findOneBy(['email' => $email->toString()]);
    }

    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function delete(User $user): void
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }

    public function existsByEmail(Email $email): bool
    {
        return $this->findByEmail($email) !== null;
    }
}