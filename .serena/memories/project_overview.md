# Project Overview

## Purpose
**Warehouse.space** is an SME-focused fulfillment platform (Flow.space alternative) built with modern PHP architecture. The platform provides comprehensive order management, inventory control, and warehouse operations for small to medium enterprises.

## Tech Stack
- **PHP 8.1+** with Symfony 6.4 LTS
- **PostgreSQL 17** as primary database
- **Redis** for caching and session management
- **Nginx** with PHP-FPM for web server
- **Docker** for containerization
- **Doctrine ORM** for database management
- **Symfony Messenger** for async processing

## Architecture
The project follows **Domain-Driven Design (DDD)** with **Clean Architecture** principles:
- **Domain Layer**: Entities, Value Objects, Repository Interfaces, Domain Services
- **Application Layer**: Use Cases, Application Services, DTOs
- **Infrastructure Layer**: ORM Repositories, 3rd-party APIs, Framework implementations
- **Interfaces Layer**: HTTP Controllers, CLI Commands, Request/Response handlers

## Key Features
- Multi-tenant architecture
- OAuth 2.0 + JWT authentication
- Role-based access control (RBAC)
- Real-time inventory tracking
- Multi-channel order aggregation
- Third-party integrations (Shopify, Amazon, etc.)
- Automated shipping and billing
- Analytics and reporting dashboard