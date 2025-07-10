# Application Service (Application Layer)

## 📄 Purpose
Application services orchestrate domain entities, services, and repositories to execute use cases.

---

## 📁 Location
```
src/Application/<BoundedContext>/Service/
```

---

## 🏗️ Gold Standard Implementation
```php
namespace App\Application\User\Service;

use App\Application\User\DTO\RegisterUserRequest;
use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use App\Domain\User\ValueObject\UserId;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use Ramsey\Uuid\Uuid;

class UserRegistrationService
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function register(RegisterUserRequest $request): void
    {
        $user = new User(
            new UserId(Uuid::uuid4()->toString()),
            new Email($request->email),
            new HashedPassword($request->password)
        );

        $this->userRepository->save($user);
    }
}
```

---

## ✅ Best Practices
- Wrap complex orchestration logic involving domain + infrastructure.
- Useful for cross-use-case services.
- Avoid domain logic here—delegate to entities or domain services.

---

## 🧩 Related
- Use Case classes may delegate to application services
- Services may be injected into other services or Symfony controllers

