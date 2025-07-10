# DTO (Application Layer)

## 📄 Purpose

Data Transfer Objects (DTOs) transport data between layers without domain logic.

---

## 📁 Location

```
src/Application/<BoundedContext>/DTO/
```

---

## 🏗️ Gold Standard Implementation

```php
namespace App\Application\User\DTO;

class RegisterUserRequest
{
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {}
}
```

---

## ✅ Best Practices

- Keep DTOs **immutable** (readonly).
- Only hold raw, validated input/output data.
- Do not include business logic.

---

## 🧩 Related Use

- `RegisterUserRequest` for input to use case
- `UserResponseDto` for returning structured data to controller

