# Project Purpose

## Base PHP Backend

A base PHP backend built with Symfony 6.4 LTS, following Domain-Driven Design (DDD) and Clean Architecture principles.

## Architecture Goals

- **Domain Layer**: Core business logic, entities, value objects, and domain services
- **Application Layer**: Use cases, application services, and DTOs
- **Infrastructure Layer**: Database repositories, external services, and adapters
- **Interfaces Layer**: HTTP controllers, CLI commands, and API endpoints
- **Shared**: Common components used across layers

## Key Principles

- Domain should be framework-agnostic
- Use dependency inversion: Application and domain should depend on interfaces only
- Use DTOs to pass data between layers
- Keep business rules in domain layer, orchestration in application layer