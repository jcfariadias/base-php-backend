# ValueObject Example (Domain Layer)

## 📄 Purpose

**Value Objects** are immutable types that represent descriptive aspects of the domain with no conceptual identity.

---

## 📁 Location

```
src/Domain/<BoundedContext>/ValueObject/
```

---

## 🏗️ Gold Standard Implementation

```php
namespace App\Domain\User\ValueObject;

class Email
{
    private string $value;

    public function __construct(string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email address.");
        }
        $this->value = strtolower($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
```

---

## ✅ Best Practices

- Should be **immutable**: no setters.
- Should be **self-validating**.
- Provide **equality check** method.
- Use in place of primitive types (e.g., `string $email` → `Email $email`).

---

## 🧩 Related Examples

- `UserId`, `HashedPassword`, `Money`, `Slug`, `PhoneNumber`, etc.

