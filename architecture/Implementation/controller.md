# Symfony Controller (Interfaces Layer)

## 📄 Purpose
Handles HTTP requests, maps them to Use Cases or Application Services, applies validation, and formats responses.

---

## 📁 Location
```
src/Interfaces/Http/<BoundedContext>/Controller/
```

## 📁 Validators Location
```
src/Application/<BoundedContext>/DTO/
```

---

## 🏗️ Gold Standard Implementation
```php
namespace App\Interfaces\Http\User\Controller;

use App\Application\User\DTO\RegisterUserRequest;
use App\Application\User\UseCase\RegisterUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;

class RegisterUserController extends AbstractController
{
    public function __construct(
        private RegisterUser $registerUser,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer
    ) {}

    #[Route('/register', name: 'user_register', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        /** @var RegisterUserRequest $dto */
        $dto = $this->serializer->deserialize($request->getContent(), RegisterUserRequest::class, 'json');

        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $violation) {
                $errorMessages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        $this->registerUser->execute($dto);

        return new JsonResponse(['status' => 'user registered'], 201);
    }
}
```

---

## ✅ Best Practices
- Lean controller: maps request to Use Case.
- Use Symfony's built-in validation via annotations on DTOs.
- Deserialize input directly into typed DTOs.

---

## 🧩 Related
- `RegisterUser` Use Case
- `RegisterUserRequest` DTO (now with validation annotations)

---

## 📌 DTO with Annotations Validation Example
```php
namespace App\Application\User\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterUserRequest
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public readonly string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 6)]
    public readonly string $password;

    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }
}
```

---

This approach uses Symfony’s serializer and validator for automatic validation of JSON payloads into DTOs with annotations.

