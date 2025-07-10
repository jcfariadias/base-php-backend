# Entity Example (Domain Layer)

## 📄 Purpose

An **Entity** is a core domain object with identity and lifecycle. It encapsulates business rules and state.

---

## 📁 Location

```
src/Domain/<BoundedContext>/Entity/
```

---

## 🏗️ Gold Standard Implementation

```php
namespace App\Domain\User\Entity;

use App\Domain\User\ValueObject\UserId;
use App\Domain\User\ValueObject\Email;
use App\Domain\User\ValueObject\HashedPassword;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid', unique: true)]
    private string $id;

    #[ORM\Embedded(class: Email::class)]
    private Email $email;

    #[ORM\Embedded(class: HashedPassword::class)]
    private HashedPassword $password;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(UserId $id, Email $email, HashedPassword $password)
    {
        $this->id = $id->getValue();
        $this->email = $email;
        $this->password = $password;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): UserId
    {
        return new UserId($this->id);
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function changeEmail(Email $newEmail): void
    {
        // business rule: check if same email, or validate
        $this->email = $newEmail;
    }

    public function getPassword(): HashedPassword
    {
        return $this->password;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
```

---

## ✅ Best Practices

- Encapsulate domain logic inside methods.
- Always use Value Objects for typed safety.
- Use Doctrine #[ORM\Embedded] or custom DBAL types to persist Value Objects.
- Avoid exposing internal properties directly.

---

## 🧩 Related Classes

- `UserId` (ValueObject)
- `Email` (ValueObject)
- `HashedPassword` (ValueObject)

