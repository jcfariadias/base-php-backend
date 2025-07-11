# Step-by-Step Deployment Guides

## 🚀 Quick Start: Top 3 Recommended Deployments

### 1. DigitalOcean App Platform (Recommended for Beginners)

#### Prerequisites
- DigitalOcean account
- Git repository (GitHub, GitLab, etc.)
- Environment variables configured

#### Step 1: Prepare Your Repository
```bash
# Ensure your .env.prod.example is complete
cp .env.prod.example .env.prod
# Edit .env.prod with production values

# Create app.yaml for DigitalOcean configuration
```

Create `app.yaml`:
```yaml
name: warehouse-app
services:
- name: web
  source_dir: /
  github:
    repo: your-username/warehouse.space
    branch: main
  run_command: |
    php bin/console doctrine:migrations:migrate --no-interaction
    php-fpm
  environment_slug: php
  instance_count: 1
  instance_size_slug: basic-xxs
  http_port: 8080
  routes:
  - path: /
  envs:
  - key: APP_ENV
    value: prod
  - key: APP_SECRET
    value: ${APP_SECRET}
  - key: DATABASE_URL
    value: ${_self.DATABASE_URL}
  - key: REDIS_URL
    value: ${redis.DATABASE_URL}

databases:
- engine: PG
  name: warehouse-db
  num_nodes: 1
  size: db-s-dev-database
  version: "13"

- engine: REDIS
  name: warehouse-redis
  num_nodes: 1
  size: db-s-dev-database
  version: "7"

workers:
- name: messenger-worker
  source_dir: /
  run_command: php bin/console messenger:consume async --limit=100
  instance_count: 1
  instance_size_slug: basic-xxs
```

#### Step 2: Deploy via DigitalOcean Dashboard
1. Go to DigitalOcean Dashboard → Apps
2. Click "Create App"
3. Connect your GitHub repository
4. Upload the `app.yaml` file
5. Configure environment variables in the dashboard
6. Click "Create Resources"

#### Step 3: Post-Deployment
```bash
# SSH into your app container (via dashboard console)
php bin/console doctrine:migrations:migrate
php bin/console cache:clear --env=prod
```

**Estimated Setup Time**: 15-30 minutes
**Monthly Cost**: ~$42 (app + database + redis)

---

### 2. Google Cloud Run (Recommended for Scalability)

#### Prerequisites
- Google Cloud account with billing enabled
- Google Cloud SDK installed
- Docker registry access (gcr.io)

#### Step 1: Prepare Cloud Build
Create `cloudbuild.yaml`:
```yaml
steps:
# Build the container image
- name: 'gcr.io/cloud-builders/docker'
  args: ['build', '-t', 'gcr.io/$PROJECT_ID/warehouse-app:$COMMIT_SHA', '.']

# Push the container image to Container Registry
- name: 'gcr.io/cloud-builders/docker'
  args: ['push', 'gcr.io/$PROJECT_ID/warehouse-app:$COMMIT_SHA']

# Deploy container image to Cloud Run
- name: 'gcr.io/google.com/cloudsdktool/cloud-sdk'
  entrypoint: gcloud
  args:
  - 'run'
  - 'deploy'
  - 'warehouse-app'
  - '--image'
  - 'gcr.io/$PROJECT_ID/warehouse-app:$COMMIT_SHA'
  - '--region'
  - 'us-central1'
  - '--platform'
  - 'managed'
  - '--allow-unauthenticated'

images:
- 'gcr.io/$PROJECT_ID/warehouse-app:$COMMIT_SHA'
```

#### Step 2: Setup Databases
```bash
# Create Cloud SQL PostgreSQL instance
gcloud sql instances create warehouse-db \
    --database-version=POSTGRES_14 \
    --tier=db-f1-micro \
    --region=us-central1

# Create database
gcloud sql databases create warehouse --instance=warehouse-db

# Create user
gcloud sql users create appuser \
    --instance=warehouse-db \
    --password=your-secure-password

# Create Redis instance
gcloud redis instances create warehouse-redis \
    --size=1 \
    --region=us-central1 \
    --redis-version=redis_6_x
```

#### Step 3: Deploy Application
```bash
# Set project
gcloud config set project YOUR_PROJECT_ID

# Build and deploy
gcloud builds submit --config cloudbuild.yaml

# Set environment variables
gcloud run services update warehouse-app \
    --set-env-vars="APP_ENV=prod,APP_SECRET=your-secret" \
    --set-env-vars="DATABASE_URL=postgresql://appuser:password@host/warehouse" \
    --set-env-vars="REDIS_URL=redis://redis-ip:6379"
```

#### Step 4: Run Migrations
```bash
# Run one-time migration job
gcloud run jobs create warehouse-migrate \
    --image=gcr.io/$PROJECT_ID/warehouse-app:latest \
    --command="php" \
    --args="bin/console,doctrine:migrations:migrate,--no-interaction"

gcloud run jobs execute warehouse-migrate
```

**Estimated Setup Time**: 45-60 minutes
**Monthly Cost**: $50-200 (based on usage)

---

### 3. Railway (Recommended for Developer Experience)

#### Prerequisites
- Railway account
- GitHub repository

#### Step 1: Connect Repository
1. Visit [railway.app](https://railway.app)
2. Sign up with GitHub
3. Click "New Project" → "Deploy from GitHub repo"
4. Select your repository

#### Step 2: Configure Services
Railway will auto-detect your Dockerfile. Add services:

1. **Add PostgreSQL**:
   - Click "New" → "Database" → "PostgreSQL"
   - Note the connection details

2. **Add Redis**:
   - Click "New" → "Database" → "Redis"
   - Note the connection details

#### Step 3: Configure Environment Variables
In your app service settings, add:
```
APP_ENV=prod
APP_SECRET=your-secret-key
DATABASE_URL=${{Postgres.DATABASE_URL}}
REDIS_URL=${{Redis.REDIS_URL}}
JWT_SECRET_KEY=your-jwt-secret
JWT_PUBLIC_KEY=your-jwt-public
JWT_PASSPHRASE=your-passphrase
```

#### Step 4: Deploy and Migrate
```bash
# Railway will auto-deploy on git push
git push origin main

# Run migrations via Railway dashboard
# Go to your app service → Deployments → Click on latest deployment → View Logs
# Use the terminal feature to run:
php bin/console doctrine:migrations:migrate --no-interaction
```

**Estimated Setup Time**: 10-20 minutes
**Monthly Cost**: ~$35-60

---

## 🔧 Production Preparation Checklist

### Before First Deployment
- [ ] Configure production environment variables
- [ ] Set up proper JWT keys
- [ ] Configure CORS settings for your domain
- [ ] Set up SSL certificates (automatic on most platforms)
- [ ] Configure database connection limits
- [ ] Set up error tracking (Sentry, Bugsnag)
- [ ] Configure logging levels

### Environment Variables Template
```bash
# Application
APP_ENV=prod
APP_SECRET=generate-random-32-char-string
APP_DEBUG=false

# Database
DATABASE_URL=postgresql://user:pass@host:5432/dbname?serverVersion=14&charset=utf8

# Redis
REDIS_URL=redis://host:6379

# JWT
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your-passphrase

# CORS (adjust for your frontend domain)
CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$

# Mailer (if using email features)
MAILER_DSN=smtp://user:pass@host:port
```

### Security Configuration
```bash
# Generate JWT keys (run locally, then upload)
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

### Performance Optimization
```bash
# Production commands to run after deployment
php bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod
php bin/console doctrine:migrations:migrate --no-interaction
```

## 🚨 Common Deployment Issues & Solutions

### Issue: Container Fails to Start
**Solution**: Check logs for PHP version, memory limits, or missing extensions
```bash
# Common fixes in Dockerfile
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql
```

### Issue: Database Connection Errors
**Solution**: Verify connection strings and network access
```bash
# Test database connection
php bin/console doctrine:schema:validate
```

### Issue: JWT Keys Missing
**Solution**: Generate and properly configure JWT keys
```bash
# Generate new keys
php bin/console lexik:jwt:generate-keypair
```

### Issue: Memory Limit Exceeded
**Solution**: Increase PHP memory limit or container resources
```php
// In php.ini or Dockerfile
memory_limit = 256M
```

## 📊 Monitoring Your Deployment

### Health Check Endpoint
Your app includes `/health` endpoint. Configure platform health checks:

**DigitalOcean**: Automatic health checks on port 8080
**Google Cloud Run**: Configure health check path `/health`
**Railway**: Automatic health monitoring

### Log Monitoring
- **Application logs**: Available in platform dashboards
- **Database logs**: Configure slow query logging
- **Error tracking**: Consider integrating Sentry

### Performance Monitoring
- Monitor response times via platform dashboards
- Set up alerts for high error rates
- Track database connection usage

This completes your deployment guide for the top 3 recommended platforms. Each offers different advantages depending on your specific needs and expertise level.