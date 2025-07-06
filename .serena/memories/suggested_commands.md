# Suggested Commands for warehouse.space

## Docker Commands
```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# View logs
docker-compose logs -f

# Rebuild containers
docker-compose up --build -d

# Access PHP container
docker-compose exec app bash

# Access PostgreSQL
docker-compose exec db psql -U symfony -d symfony
```

## Symfony Commands
```bash
# Clear cache
php bin/console cache:clear

# Create database
php bin/console doctrine:database:create

# Run migrations
php bin/console doctrine:migrations:migrate

# Generate migration
php bin/console doctrine:migrations:generate

# Create entity
php bin/console make:entity

# Create controller
php bin/console make:controller

# Debug routes
php bin/console debug:router

# Debug container
php bin/console debug:container
```

## Composer Commands
```bash
# Install dependencies
composer install

# Update dependencies
composer update

# Add new package
composer require package/name

# Add dev dependency
composer require --dev package/name

# Generate autoload
composer dump-autoload
```

## Development Commands
```bash
# Start Symfony server (development)
symfony server:start

# Check security vulnerabilities
symfony security:check

# Run tests
php bin/phpunit

# Check code style
php-cs-fixer fix --dry-run

# Static analysis
phpstan analyse
```

## Database Commands
```bash
# Create new migration
php bin/console doctrine:migrations:generate

# Execute migrations
php bin/console doctrine:migrations:migrate

# Check schema
php bin/console doctrine:schema:validate

# Generate entities from database
php bin/console doctrine:mapping:import --force App xml --path=src/Entity

# Update database schema
php bin/console doctrine:schema:update --force
```