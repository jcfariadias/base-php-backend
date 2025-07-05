# Domain Service (Domain Layer)

## 📄 Purpose

Encapsulates domain logic that doesn't naturally fit in a single entity.

---

## 📁 Location

```
src/Domain/<BoundedContext>/Service/
```

---

## 🏗️ Gold Standard Implementation

```php
namespace App\Domain\User\Service;

use App\Domain\User\Entity\User;

class UniqueEmailChecker
{
    private iterable $existingEmails;

    public function __construct(iterable $existingEmails)
    {
        $this->existingEmails = $existingEmails;
    }

    public function isUnique(string $email): bool
    {
        return !in_array(strtolower($email), array_map('strtolower', $this->existingEmails), true);
    }
}
```

---

## ✅ Best Practices

- Should contain **pure business logic**.
- Should not know about application or infrastructure.
- Often stateless; or uses injected domain collaborators.

---

## 🧩 Common Examples

- `UniqueEmailChecker`
- `PasswordStrengthValidator`
- `CreditLimitCalculator`

