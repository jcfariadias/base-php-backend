# Docker and Infrastructure Setup

## Current Docker Configuration
The project uses **Docker Compose** with the following services:

### Services
1. **nginx**: Web server (port 8000)
2. **app**: PHP-FPM application container
3. **db**: PostgreSQL 17 database (port 5432)

### Database Configuration
- **PostgreSQL 17** Alpine image
- Database: `symfony`
- User: `symfony`
- Password: `symfony`
- Port: `5432`

### Environment Variables
```env
APP_ENV=dev
APP_SECRET=your-secret-key-here
DATABASE_URL=postgresql://symfony:symfony@db:5432/symfony?serverVersion=17&charset=utf8
```

## Docker Commands
```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f app

# Access containers
docker-compose exec app bash
docker-compose exec db psql -U symfony -d symfony

# Rebuild
docker-compose up --build -d
```

## Volumes and Persistence
- **pgdata**: PostgreSQL data persistence
- **Application code**: Mounted to `/var/www/html`

## Network Configuration
- All services on same Docker network
- Internal communication via service names
- External access via mapped ports

## Redis Configuration
- **Redis needs to be added** for caching infrastructure
- Should be configured for session storage
- Use for Symfony Messenger queues