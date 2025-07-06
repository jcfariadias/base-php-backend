<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use InvalidArgumentException;

final class Email
{
    private readonly string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        $trimmedValue = trim($value);
        
        if (empty($trimmedValue)) {
            throw new InvalidArgumentException('Email cannot be empty');
        }

        if (strlen($trimmedValue) > 254) {
            throw new InvalidArgumentException('Email is too long (maximum 254 characters)');
        }

        if (!filter_var($trimmedValue, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }

        return new self(strtolower($trimmedValue));
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    public function getDomain(): string
    {
        $atPosition = strpos($this->value, '@');
        if ($atPosition === false) {
            throw new \LogicException('Invalid email format - no @ symbol found');
        }
        
        return substr($this->value, $atPosition + 1);
    }

    public function getLocalPart(): string
    {
        $atPosition = strpos($this->value, '@');
        if ($atPosition === false) {
            throw new \LogicException('Invalid email format - no @ symbol found');
        }
        
        return substr($this->value, 0, $atPosition);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    // Prevent cloning to maintain immutability
    public function __clone()
    {
        throw new \BadMethodCallException('Email is immutable and cannot be cloned');
    }

    // Prevent unserialization to maintain immutability
    public function __wakeup()
    {
        throw new \BadMethodCallException('Email cannot be unserialized');
    }
}