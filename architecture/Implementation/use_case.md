# Use Case (Application Layer)

## 📄 Purpose
Coordinates application logic to fulfill a single user-driven goal, like "Register User".

---

## 📁 Location
```
src/Application/<BoundedContext>/UseCase/
```

---

## 🏗️ Gold Standard Implementation
```php
namespace App\Application\User\UseCase;

use App\Application\User\DTO\RegisterUserRequest;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use App\Domain\User\ValueObject\UserId;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use Ramsey\Uuid\Uuid;

class RegisterUser
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function execute(RegisterUserRequest $request): void
    {
        $user = new User(
            new UserId(Uuid::uuid4()->toString()),
            new Email($request->email),
            new HashedPassword($request->password) // assume hashed
        );

        $this->userRepository->save($user);
    }
}
```

---

## ✅ Best Practices
- One class per use case.
- Should coordinate domain and repositories.
- Should use DTOs for inputs/outputs.
- Should not contain domain logic (that belongs in Domain layer).

---

## 🧩 Related
- `RegisterUserRequest` (DTO)
- `UserRepository` (Interface)

