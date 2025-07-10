# Repository Implementation (Infrastructure Layer)

## 📄 Purpose
Implements the repository interface using a specific technology (e.g. Doctrine ORM).

---

## 📁 Location
```
src/Infrastructure/<BoundedContext>/Repository/
```

---

## 🏗️ Gold Standard Implementation
```php
namespace App\Infrastructure\User\Repository;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepository;
use App\Domain\User\ValueObject\UserId;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class DoctrineUserRepository implements UserRepository
{
    private ObjectRepository $repository;

    public function __construct(private EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(User::class);
    }

    public function save(User $user): void
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    public function findById(UserId $id): ?User
    {
        return $this->repository->find($id->getValue());
    }

    public function findByEmail(string $email): ?User
    {
        return $this->repository->findOneBy(['email.value' => strtolower($email)]);
    }

    public function delete(User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }
}
```

---

## ✅ Best Practices
- Implements only the domain interface.
- Do not expose ORM specifics to domain or application.
- Keep ORM configuration (mapping, metadata) out of core logic.

---

## 🧩 Related
- Depends on `UserRepository` interface (Domain)
- Can be wired using Symfony's autowiring in `services.yaml` or attributes

