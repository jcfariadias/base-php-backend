# Docker Setup

## Container Services

### 1. Nginx (nginx:alpine)
- **Port**: 8000:80
- **Volume**: Application code + configuration
- **Config**: `/docker/nginx/nginx.conf`
- **Features**: Security headers, Gzip compression, Static assets caching, Health check endpoint

### 2. PHP-FPM App (Custom Dockerfile)
- **Base**: php:8.4-fpm
- **Port**: 9000 (internal)
- **Volume**: Application code
- **Environment**: DATABASE_URL, APP_ENV, APP_SECRET
- **Features**: Composer, Extensions, Optimized build

### 3. PostgreSQL Database (postgres:17-alpine)
- **Port**: 5432:5432
- **Volume**: pgdata (persistent)
- **Credentials**: symfony/symfony
- **Database**: symfony

## Key Configuration Files

- `docker-compose.yml`: Service orchestration
- `Dockerfile`: PHP-FPM container build
- `docker/nginx/nginx.conf`: Nginx configuration
- `.env`: Environment variables