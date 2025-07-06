# Base PHP Backend

A base PHP backend built with Symfony 6.4 LTS, following Domain-Driven Design (DDD) and Clean Architecture principles.

## Architecture

This project implements a layered architecture with clear separation of concerns:

- **Domain Layer**: Core business logic, entities, value objects, and domain services
- **Application Layer**: Use cases, application services, and DTOs
- **Infrastructure Layer**: Database repositories, external services, and adapters
- **Interfaces Layer**: HTTP controllers, CLI commands, and API endpoints
- **Shared**: Common components used across layers

## Tech Stack

- **PHP**: 8.4 FPM
- **Web Server**: Nginx (Alpine)
- **Framework**: Symfony 6.4 LTS
- **Database**: PostgreSQL 17 (Latest)
- **ORM**: Doctrine ORM
- **Containerization**: Docker & Docker Compose

## Quick Start

### Prerequisites

- Docker and Docker Compose
- Git

### Setup

1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd base-php-backend
   ```

2. Build and start the containers:
   ```bash
   docker compose up -d
   ```

3. Install dependencies:
   ```bash
   docker compose exec app composer install
   ```

4. Run database migrations:
   ```bash
   docker compose exec app php bin/console doctrine:migrations:migrate
   ```

5. Access the application:
   - API: http://localhost:8000
   - Database: localhost:5432 (symfony/symfony)

## Development Commands

### Docker Commands
```bash
# Start services
docker compose up -d

# Stop services
docker compose down

# View logs
docker compose logs app

# Access container shell
docker compose exec app bash
```

### Symfony Commands
```bash
# Run inside container
docker compose exec app <command>

# Console commands
php bin/console list
php bin/console cache:clear
php bin/console debug:router

# Database commands
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
php bin/console doctrine:schema:validate
```

## Project Structure

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
│   ├── Adapter/         # External service adapters
│   └── Persistence/
│       └── Doctrine/    # Doctrine mappings
├── Interfaces/          # Interface layer
│   ├── Http/
│   │   ├── Controller/  # HTTP controllers
│   │   ├── Request/     # Request DTOs
│   │   └── Response/    # Response DTOs
│   ├── Cli/             # CLI commands
│   └── Event/           # Event listeners
└── Shared/              # Shared components
    ├── Domain/
    ├── Application/
    ├── Infrastructure/
    └── Interfaces/
```

## Environment Variables

Key environment variables in `.env`:

- `APP_ENV`: Application environment (dev/prod/test)
- `APP_SECRET`: Application secret key
- `DATABASE_URL`: Database connection string

## Contributing

1. Follow PSR-12 coding standards
2. Write tests for new features
3. Ensure clean architecture principles are maintained
4. Keep domain layer framework-agnostic

## License

MIT License
