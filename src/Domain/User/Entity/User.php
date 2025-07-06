<?php

declare(strict_types=1);

namespace App\Domain\User\Entity;

use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\UserId;
use App\Domain\User\ValueObject\UserStatus;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string', length: 20)]
    private string $status;

    #[ORM\Column(type: 'string', length: 36, nullable: true)]
    private ?string $tenantId = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        UserId $id,
        Email $email,
        string $password,
        array $roles = ['ROLE_USER'],
        ?string $tenantId = null
    ) {
        $this->id = $id->toString();
        $this->email = $email->toString();
        $this->password = $password;
        $this->roles = $roles;
        $this->status = UserStatus::active()->toString();
        $this->tenantId = $tenantId;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): UserId
    {
        return UserId::fromString($this->id);
    }

    public function getEmail(): Email
    {
        return Email::fromString($this->email);
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
        $this->touch();
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
        $this->touch();
    }

    public function getStatus(): UserStatus
    {
        return UserStatus::fromString($this->status);
    }

    public function setStatus(UserStatus $status): void
    {
        $this->status = $status->toString();
        $this->touch();
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

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function isActive(): bool
    {
        return $this->getStatus()->isActive();
    }

    public function isSuspended(): bool
    {
        return $this->getStatus()->isSuspended();
    }

    public function isDeleted(): bool
    {
        return $this->getStatus()->isDeleted();
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}