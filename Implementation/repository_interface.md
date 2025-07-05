# Repository Interface (Domain Layer)

## 📄 Purpose

Defines the contract for persisting and retrieving domain entities. Used by Application layer.

---

## 📁 Location

```
src/Domain/<BoundedContext>/Repository/
```

---

## 🏗️ Gold Standard Implementation

```php
namespace App\Domain\User\Repository;

use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\UserId;

interface UserRepository
{
    public function save(User $user): void;

    public function findById(UserId $id): ?User;

    public function findByEmail(string $email): ?User;

    public function delete(User $user): void;
}
```

---

## ✅ Best Practices

- No infrastructure logic or knowledge (e.g., no ORM logic).
- Should return domain entities or value objects only.
- Designed for **dependency inversion**.

---

## 🧩 Related Implementations

- `DoctrineUserRepository` (Infrastructure)
- `InMemoryUserRepository` (Testing)

