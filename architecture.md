# Symfony DDD + Clean Architecture Base

This document provides a high-level architectural guide for building Symfony applications using Domain-Driven Design (DDD) and Clean Architecture principles.

---

## 📐 Layered Architecture Overview

```
┌────────────────────────────┐
│        Interfaces          │  (Symfony Controllers, CLI, etc.)
└────────────────────────────┘
            ↓
┌────────────────────────────┐
│    Application Layer       │  (Use Cases, Services, DTOs)
└────────────────────────────┘
            ↓
┌────────────────────────────┐
│       Domain Layer         │  (Entities, Value Objects, Repositories, Domain Services)
└────────────────────────────┘
            ↓
┌────────────────────────────┐
│   Infrastructure Layer     │  (Doctrine, Symfony Components, Adapters)
└────────────────────────────┘
```

---

## 📁 Folder Structure

```
src/
├── Domain/
│   └── <BoundedContext>/
│       ├── Entity/
│       ├── ValueObject/
│       ├── Repository/
│       ├── Service/
│       └── Event/
│
├── Application/
│   └── <BoundedContext>/
│       ├── UseCase/
│       ├── DTO/
│       └── Service/
│
├── Infrastructure/
│   └── <BoundedContext>/
│       ├── Repository/
│       ├── Service/
│       └── Adapter/
│
├── Interfaces/
│   ├── Http/
│   │   └── <BoundedContext>/
│   │       ├── Controller/
│   │       ├── Request/
│   │       └── Response/
│   ├── Cli/
│   └── Event/
│
└── Shared/
    ├── Domain/
    ├── Application/
    ├── Infrastructure/
    └── Interfaces/
```

---

## ✅ Responsibilities Per Layer

### 1. **Domain Layer**

- Entities
- Value Objects
- Domain Services
- Repository Interfaces
- Domain Events

### 2. **Application Layer**

- Use Cases
- Application Services
- Input/Output DTOs

### 3. **Infrastructure Layer**

- ORM Repositories (Doctrine, etc.)
- 3rd-party APIs
- Framework-specific implementations

### 4. **Interfaces Layer**

- HTTP Controllers
- CLI Commands
- UI/Input interfaces
- Request/Response Mappers

---

## 🧩 Optional Patterns to Use

- CQRS (Command/Query Responsibility Segregation)
- Event Sourcing
- Hexagonal Architecture
- Symfony Messenger for Domain/Application Events

---

## 🐳 Containerization Requirements

- The application must be containerized using **Docker**.
- Use **PostgreSQL** as the primary database.
- Use **PHP 8.4** as the minimum PHP runtime version.
- Use the **latest Symfony LTS version** (e.g., Symfony 6.4 LTS).
- Required Docker setup files:
  - `Dockerfile`
  - `docker-compose.yml`
  - `.env`

### Example `docker-compose.yml`
```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "8000:8000"
    volumes:
      - .:/app
    depends_on:
      - db
    environment:
      DATABASE_URL: postgresql://symfony:symfony@db:5432/symfony

  db:
    image: postgres:14-alpine
    environment:
      POSTGRES_DB: symfony
      POSTGRES_USER: symfony
      POSTGRES_PASSWORD: symfony
    ports:
      - "5432:5432"
    volumes:
      - pgdata:/var/lib/postgresql/data

volumes:
  pgdata:
```

---

## 🏁 Recommendations

- **Domain should be framework-agnostic.**
- **Use dependency inversion:** Application and domain should depend on interfaces only.
- **Use DTOs to pass data between layers.**
- **Keep business rules in domain layer, orchestration in application layer.**

---

## 🚀 Ready to Extend

Use this architecture as a boilerplate to kick off any Symfony-based backend project in a maintainable and scalable way.

---

## Next

Explore each class type and its ideal implementation in the linked documents:

- [Entity](entity.md)
- [ValueObject](valueobject.md)
- [Repository Interface](repository_interface.md)
- [Domain Service](domain_service.md)
- [Use Case](use_case.md)
- [DTO](dto.md)
- [Application Service](application_service.md)
- [Controller](controller.md)
- [Repository Implementation](repository_impl.md)

