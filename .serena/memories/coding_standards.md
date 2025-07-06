# Coding Standards and Conventions

## Code Style

- **PSR-12**: Follow PSR-12 coding standards
- **Framework**: Symfony best practices
- **Architecture**: Clean Architecture and DDD principles

## Naming Conventions

- **Classes**: PascalCase
- **Methods**: camelCase
- **Variables**: camelCase
- **Constants**: UPPER_CASE
- **Files**: Match class names

## Directory Structure

```
src/
├── Domain/              # Core business logic
│   ├── Entity/          # Domain entities
│   ├── ValueObject/     # Value objects
│   ├── Repository/      # Repository interfaces
│   ├── Service/         # Domain services
│   └── Event/           # Domain events
├── Application/         # Application layer
│   ├── UseCase/         # Use cases
│   ├── DTO/             # Data transfer objects
│   └── Service/         # Application services
├── Infrastructure/      # Infrastructure layer
│   ├── Repository/      # Repository implementations
│   ├── Service/         # Infrastructure services
│   └── Adapter/         # External service adapters
├── Interfaces/          # Interface layer
│   ├── Http/Controller/ # HTTP controllers
│   ├── Cli/             # CLI commands
│   └── Event/           # Event listeners
└── Shared/              # Shared components
```

## Architecture Rules

- Domain should be framework-agnostic
- Use dependency inversion
- Use DTOs to pass data between layers
- Keep business rules in domain layer
- Keep orchestration in application layer