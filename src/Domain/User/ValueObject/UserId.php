<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class UserId
{
    private readonly UuidInterface $value;

    private function __construct(UuidInterface $value)
    {
        $this->value = $value;
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4());
    }

    public static function fromString(string $value): self
    {
        // Trim whitespace to handle edge cases
        $trimmedValue = trim($value);
        
        if (empty($trimmedValue)) {
            throw new InvalidArgumentException('User ID cannot be empty');
        }

        if (!Uuid::isValid($trimmedValue)) {
            throw new InvalidArgumentException('Invalid UUID format for User ID');
        }

        return new self(Uuid::fromString($trimmedValue));
    }

    public function toString(): string
    {
        return $this->value->toString();
    }

    public function equals(UserId $other): bool
    {
        return $this->value->equals($other->value);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    // Prevent cloning to maintain immutability
    public function __clone()
    {
        throw new \BadMethodCallException('UserId is immutable and cannot be cloned');
    }

    // Prevent unserialization to maintain immutability
    public function __wakeup()
    {
        throw new \BadMethodCallException('UserId cannot be unserialized');
    }
}