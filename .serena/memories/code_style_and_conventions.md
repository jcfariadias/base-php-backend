# Code Style and Conventions

## PHP Standards
- Follow **PSR-12** coding standards
- Use **PHP 8.1+** features (typed properties, attributes, etc.)
- Strict typing: `declare(strict_types=1);`
- Use **type hints** for all parameters and return types

## Architecture Patterns
- **Domain-Driven Design (DDD)** with bounded contexts
- **Clean Architecture** with clear layer separation
- **Hexagonal Architecture** for external dependencies
- **CQRS** pattern for command/query separation

## Naming Conventions
- **Classes**: PascalCase (`OrderEntity`, `UserService`)
- **Methods**: camelCase (`createOrder`, `getUserById`)
- **Properties**: camelCase (`$orderId`, `$customerName`)
- **Constants**: UPPER_SNAKE_CASE (`MAX_RETRY_COUNT`)
- **Database**: snake_case (`order_items`, `user_id`)

## File Organization
```
src/
├── Domain/           # Domain entities and business logic
├── Application/      # Use cases and application services
├── Infrastructure/   # External dependencies and implementations
├── Interfaces/       # Controllers and external interfaces
└── Shared/          # Common utilities and shared code
```

## Entity Attributes
- Use **Doctrine Attributes** instead of annotations
- Place entity mappings in `Infrastructure/Persistence/Doctrine`
- Keep domain entities framework-agnostic

## Documentation
- Use **PHPDoc** for all classes and methods
- Include `@param` and `@return` tags
- Add `@throws` for exceptions
- Document business rules and constraints

## Testing
- **Unit tests** for domain logic
- **Integration tests** for use cases
- **API tests** for controllers
- Use **PHPUnit** with data providers
- Mock external dependencies