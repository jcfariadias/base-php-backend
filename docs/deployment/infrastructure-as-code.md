# Infrastructure as Code Examples

## 📁 File Organization
```
deployment/
├── docker-compose/
│   ├── production.yml
│   └── staging.yml
├── kubernetes/
│   ├── namespace.yaml
│   ├── configmap.yaml
│   ├── secrets.yaml
│   ├── deployment.yaml
│   ├── service.yaml
│   └── ingress.yaml
├── terraform/
│   ├── aws/
│   ├── gcp/
│   └── digitalocean/
└── cloud-configs/
    ├── digitalocean-app.yaml
    ├── railway-service.yaml
    └── gcp-cloudrun.yaml
```

## 🐳 Docker Compose Configurations

### Production Docker Compose
**File**: `deployment/docker-compose/production.yml`

```yaml
version: '3.8'

services:
  app:
    image: your-registry/warehouse-app:${TAG:-latest}
    restart: unless-stopped
    depends_on:
      - db
      - redis
    environment:
      - APP_ENV=prod
      - APP_SECRET=${APP_SECRET}
      - DATABASE_URL=postgresql://warehouse:${DB_PASSWORD}@db:5432/warehouse
      - REDIS_URL=redis://redis:6379
      - JWT_SECRET_KEY=/var/jwt/private.pem
      - JWT_PUBLIC_KEY=/var/jwt/public.pem
      - JWT_PASSPHRASE=${JWT_PASSPHRASE}
    volumes:
      - ./jwt:/var/jwt:ro
      - app_data:/var/www/html/var
    networks:
      - app-network
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.warehouse.rule=Host(`your-domain.com`)"
      - "traefik.http.routers.warehouse.tls=true"
      - "traefik.http.routers.warehouse.tls.certresolver=letsencrypt"

  nginx:
    image: nginx:alpine
    restart: unless-stopped
    volumes:
      - ./nginx/nginx.conf:/etc/nginx/nginx.conf:ro
      - ./nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
    depends_on:
      - app
    networks:
      - app-network
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.nginx.rule=Host(`your-domain.com`)"
      - "traefik.http.services.nginx.loadbalancer.server.port=80"

  db:
    image: postgres:15-alpine
    restart: unless-stopped
    environment:
      - POSTGRES_DB=warehouse
      - POSTGRES_USER=warehouse
      - POSTGRES_PASSWORD=${DB_PASSWORD}
    volumes:
      - postgres_data:/var/lib/postgresql/data
    networks:
      - app-network
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U warehouse"]
      interval: 30s
      timeout: 10s
      retries: 3

  redis:
    image: redis:7-alpine
    restart: unless-stopped
    command: redis-server --appendonly yes --requirepass ${REDIS_PASSWORD}
    volumes:
      - redis_data:/data
    networks:
      - app-network
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 30s
      timeout: 10s
      retries: 3

  traefik:
    image: traefik:v2.10
    restart: unless-stopped
    command:
      - "--api.dashboard=true"
      - "--providers.docker=true"
      - "--providers.docker.exposedbydefault=false"
      - "--entrypoints.web.address=:80"
      - "--entrypoints.websecure.address=:443"
      - "--certificatesresolvers.letsencrypt.acme.email=your-email@domain.com"
      - "--certificatesresolvers.letsencrypt.acme.storage=/letsencrypt/acme.json"
      - "--certificatesresolvers.letsencrypt.acme.httpchallenge.entrypoint=web"
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock:ro
      - traefik_data:/letsencrypt
    networks:
      - app-network

volumes:
  postgres_data:
  redis_data:
  app_data:
  traefik_data:

networks:
  app-network:
    driver: bridge
```

### Staging Environment
**File**: `deployment/docker-compose/staging.yml`

```yaml
version: '3.8'

services:
  app:
    build: 
      context: ../../
      dockerfile: Dockerfile
    environment:
      - APP_ENV=dev
      - APP_DEBUG=true
      - DATABASE_URL=postgresql://warehouse:staging@db:5432/warehouse_staging
      - REDIS_URL=redis://redis:6379
    volumes:
      - ../../:/var/www/html
    ports:
      - "8080:8080"
    depends_on:
      - db
      - redis

  db:
    image: postgres:15-alpine
    environment:
      - POSTGRES_DB=warehouse_staging
      - POSTGRES_USER=warehouse
      - POSTGRES_PASSWORD=staging
    ports:
      - "5433:5432"
    volumes:
      - staging_postgres_data:/var/lib/postgresql/data

  redis:
    image: redis:7-alpine
    ports:
      - "6380:6379"
    volumes:
      - staging_redis_data:/data

volumes:
  staging_postgres_data:
  staging_redis_data:
```

## ☸️ Kubernetes Configurations

### Namespace
**File**: `deployment/kubernetes/namespace.yaml`

```yaml
apiVersion: v1
kind: Namespace
metadata:
  name: warehouse
  labels:
    name: warehouse
```

### ConfigMap
**File**: `deployment/kubernetes/configmap.yaml`

```yaml
apiVersion: v1
kind: ConfigMap
metadata:
  name: warehouse-config
  namespace: warehouse
data:
  APP_ENV: "prod"
  DATABASE_URL: "postgresql://warehouse:password@postgres-service:5432/warehouse"
  REDIS_URL: "redis://redis-service:6379"
  CORS_ALLOW_ORIGIN: "^https?://(localhost|127\\.0\\.0\\.1)(:[0-9]+)?$"
```

### Secrets
**File**: `deployment/kubernetes/secrets.yaml`

```yaml
apiVersion: v1
kind: Secret
metadata:
  name: warehouse-secrets
  namespace: warehouse
type: Opaque
data:
  APP_SECRET: <base64-encoded-secret>
  JWT_PASSPHRASE: <base64-encoded-passphrase>
  DB_PASSWORD: <base64-encoded-password>
  REDIS_PASSWORD: <base64-encoded-password>
```

### Application Deployment
**File**: `deployment/kubernetes/deployment.yaml`

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: warehouse-app
  namespace: warehouse
  labels:
    app: warehouse
spec:
  replicas: 3
  selector:
    matchLabels:
      app: warehouse
  template:
    metadata:
      labels:
        app: warehouse
    spec:
      containers:
      - name: app
        image: your-registry/warehouse-app:latest
        ports:
        - containerPort: 8080
        env:
        - name: APP_ENV
          valueFrom:
            configMapKeyRef:
              name: warehouse-config
              key: APP_ENV
        - name: APP_SECRET
          valueFrom:
            secretKeyRef:
              name: warehouse-secrets
              key: APP_SECRET
        - name: DATABASE_URL
          valueFrom:
            configMapKeyRef:
              name: warehouse-config
              key: DATABASE_URL
        - name: REDIS_URL
          valueFrom:
            configMapKeyRef:
              name: warehouse-config
              key: REDIS_URL
        resources:
          requests:
            memory: "256Mi"
            cpu: "250m"
          limits:
            memory: "512Mi"
            cpu: "500m"
        livenessProbe:
          httpGet:
            path: /health
            port: 8080
          initialDelaySeconds: 30
          periodSeconds: 10
        readinessProbe:
          httpGet:
            path: /health
            port: 8080
          initialDelaySeconds: 5
          periodSeconds: 5

---
apiVersion: apps/v1
kind: Deployment
metadata:
  name: postgres
  namespace: warehouse
spec:
  replicas: 1
  selector:
    matchLabels:
      app: postgres
  template:
    metadata:
      labels:
        app: postgres
    spec:
      containers:
      - name: postgres
        image: postgres:15-alpine
        ports:
        - containerPort: 5432
        env:
        - name: POSTGRES_DB
          value: warehouse
        - name: POSTGRES_USER
          value: warehouse
        - name: POSTGRES_PASSWORD
          valueFrom:
            secretKeyRef:
              name: warehouse-secrets
              key: DB_PASSWORD
        volumeMounts:
        - name: postgres-storage
          mountPath: /var/lib/postgresql/data
      volumes:
      - name: postgres-storage
        persistentVolumeClaim:
          claimName: postgres-pvc

---
apiVersion: apps/v1
kind: Deployment
metadata:
  name: redis
  namespace: warehouse
spec:
  replicas: 1
  selector:
    matchLabels:
      app: redis
  template:
    metadata:
      labels:
        app: redis
    spec:
      containers:
      - name: redis
        image: redis:7-alpine
        ports:
        - containerPort: 6379
        command: ["redis-server", "--requirepass", "$(REDIS_PASSWORD)"]
        env:
        - name: REDIS_PASSWORD
          valueFrom:
            secretKeyRef:
              name: warehouse-secrets
              key: REDIS_PASSWORD
        volumeMounts:
        - name: redis-storage
          mountPath: /data
      volumes:
      - name: redis-storage
        persistentVolumeClaim:
          claimName: redis-pvc
```

### Services
**File**: `deployment/kubernetes/service.yaml`

```yaml
apiVersion: v1
kind: Service
metadata:
  name: warehouse-service
  namespace: warehouse
spec:
  selector:
    app: warehouse
  ports:
  - protocol: TCP
    port: 80
    targetPort: 8080
  type: ClusterIP

---
apiVersion: v1
kind: Service
metadata:
  name: postgres-service
  namespace: warehouse
spec:
  selector:
    app: postgres
  ports:
  - protocol: TCP
    port: 5432
    targetPort: 5432
  type: ClusterIP

---
apiVersion: v1
kind: Service
metadata:
  name: redis-service
  namespace: warehouse
spec:
  selector:
    app: redis
  ports:
  - protocol: TCP
    port: 6379
    targetPort: 6379
  type: ClusterIP
```

### Ingress
**File**: `deployment/kubernetes/ingress.yaml`

```yaml
apiVersion: networking.k8s.io/v1
kind: Ingress
metadata:
  name: warehouse-ingress
  namespace: warehouse
  annotations:
    kubernetes.io/ingress.class: nginx
    cert-manager.io/cluster-issuer: letsencrypt-prod
    nginx.ingress.kubernetes.io/ssl-redirect: "true"
spec:
  tls:
  - hosts:
    - your-domain.com
    secretName: warehouse-tls
  rules:
  - host: your-domain.com
    http:
      paths:
      - path: /
        pathType: Prefix
        backend:
          service:
            name: warehouse-service
            port:
              number: 80
```

## ☁️ Cloud Platform Configurations

### DigitalOcean App Platform
**File**: `deployment/cloud-configs/digitalocean-app.yaml`

```yaml
name: warehouse-app
services:
- name: web
  source_dir: /
  github:
    repo: your-username/warehouse-space
    branch: main
  run_command: |
    php bin/console doctrine:migrations:migrate --no-interaction
    docker-php-entrypoint php-fpm
  build_command: |
    composer install --no-dev --optimize-autoloader
    php bin/console cache:clear --env=prod
  environment_slug: php
  instance_count: 2
  instance_size_slug: basic-s
  http_port: 8080
  health_check:
    http_path: /health
  routes:
  - path: /
  envs:
  - key: APP_ENV
    value: prod
  - key: APP_SECRET
    value: ${APP_SECRET}
  - key: DATABASE_URL
    value: ${db.DATABASE_URL}
  - key: REDIS_URL
    value: ${redis.DATABASE_URL}
  - key: JWT_SECRET_KEY
    value: ${JWT_SECRET_KEY}
  - key: JWT_PUBLIC_KEY
    value: ${JWT_PUBLIC_KEY}
  - key: JWT_PASSPHRASE
    value: ${JWT_PASSPHRASE}

- name: worker
  source_dir: /
  run_command: php bin/console messenger:consume async --limit=100
  environment_slug: php
  instance_count: 1
  instance_size_slug: basic-xxs

databases:
- engine: PG
  name: warehouse-db
  num_nodes: 1
  size: db-s-1vcpu-1gb
  version: "15"

- engine: REDIS
  name: warehouse-redis
  num_nodes: 1
  size: db-s-1vcpu-1gb
  version: "7"

alerts:
- rule: CPU_UTILIZATION
  disabled: false
  value: 80
- rule: MEM_UTILIZATION
  disabled: false
  value: 80
```

### Google Cloud Run
**File**: `deployment/cloud-configs/gcp-cloudrun.yaml`

```yaml
apiVersion: serving.knative.dev/v1
kind: Service
metadata:
  name: warehouse-app
  annotations:
    run.googleapis.com/ingress: all
    run.googleapis.com/execution-environment: gen2
spec:
  template:
    metadata:
      annotations:
        autoscaling.knative.dev/minScale: "1"
        autoscaling.knative.dev/maxScale: "100"
        run.googleapis.com/cpu-throttling: "false"
        run.googleapis.com/memory: "512Mi"
        run.googleapis.com/cpu: "1000m"
    spec:
      containers:
      - image: gcr.io/your-project/warehouse-app:latest
        ports:
        - containerPort: 8080
        env:
        - name: APP_ENV
          value: prod
        - name: APP_SECRET
          valueFrom:
            secretKeyRef:
              name: app-secrets
              key: app-secret
        - name: DATABASE_URL
          valueFrom:
            secretKeyRef:
              name: app-secrets
              key: database-url
        - name: REDIS_URL
          valueFrom:
            secretKeyRef:
              name: app-secrets
              key: redis-url
        resources:
          limits:
            memory: 512Mi
            cpu: 1000m
        livenessProbe:
          httpGet:
            path: /health
            port: 8080
          initialDelaySeconds: 30
          periodSeconds: 10
        startupProbe:
          httpGet:
            path: /health
            port: 8080
          initialDelaySeconds: 10
          periodSeconds: 5
          failureThreshold: 10
```

### Railway Configuration
**File**: `deployment/cloud-configs/railway-service.yaml`

```yaml
version: 2

build:
  builder: DOCKERFILE
  dockerfilePath: Dockerfile

deploy:
  startCommand: php-fpm
  healthcheckPath: /health
  restartPolicyType: ON_FAILURE
  restartPolicyMaxRetries: 10

environments:
  production:
    variables:
      APP_ENV: prod
      APP_DEBUG: false
      NODE_ENV: production

services:
  app:
    build:
      dockerfile: Dockerfile
    variables:
      APP_ENV: prod
      DATABASE_URL: ${{Postgres.DATABASE_URL}}
      REDIS_URL: ${{Redis.REDIS_URL}}
    healthcheck:
      path: /health
      interval: 30
      timeout: 10
      retries: 3

  postgres:
    image: postgres:15
    variables:
      POSTGRES_DB: warehouse
      POSTGRES_USER: warehouse
      POSTGRES_PASSWORD: ${{POSTGRES_PASSWORD}}

  redis:
    image: redis:7-alpine
```

## 🔧 Infrastructure Deployment Scripts

### Kubernetes Deployment Script
**File**: `scripts/deploy-k8s.sh`

```bash
#!/bin/bash
set -e

NAMESPACE="warehouse"
KUBECTL_CMD="kubectl"

echo "🚀 Deploying Warehouse App to Kubernetes"

# Create namespace
echo "📁 Creating namespace..."
$KUBECTL_CMD apply -f deployment/kubernetes/namespace.yaml

# Apply secrets (make sure to create these first)
echo "🔐 Applying secrets..."
$KUBECTL_CMD apply -f deployment/kubernetes/secrets.yaml

# Apply configmaps
echo "⚙️ Applying configmaps..."
$KUBECTL_CMD apply -f deployment/kubernetes/configmap.yaml

# Apply persistent volumes
echo "💾 Creating persistent volumes..."
$KUBECTL_CMD apply -f deployment/kubernetes/pv.yaml

# Deploy applications
echo "🐳 Deploying applications..."
$KUBECTL_CMD apply -f deployment/kubernetes/deployment.yaml

# Create services
echo "🌐 Creating services..."
$KUBECTL_CMD apply -f deployment/kubernetes/service.yaml

# Create ingress
echo "🔗 Creating ingress..."
$KUBECTL_CMD apply -f deployment/kubernetes/ingress.yaml

# Wait for rollout
echo "⏳ Waiting for deployment to complete..."
$KUBECTL_CMD rollout status deployment/warehouse-app -n $NAMESPACE
$KUBECTL_CMD rollout status deployment/postgres -n $NAMESPACE
$KUBECTL_CMD rollout status deployment/redis -n $NAMESPACE

# Run migrations
echo "🗄️ Running database migrations..."
$KUBECTL_CMD exec -n $NAMESPACE deployment/warehouse-app -- php bin/console doctrine:migrations:migrate --no-interaction

echo "✅ Deployment complete!"
echo "🔍 Check status with: kubectl get pods -n $NAMESPACE"
```

### Docker Compose Deployment Script
**File**: `scripts/deploy-docker.sh`

```bash
#!/bin/bash
set -e

ENVIRONMENT=${1:-production}
COMPOSE_FILE="deployment/docker-compose/${ENVIRONMENT}.yml"

echo "🚀 Deploying Warehouse App with Docker Compose"
echo "📄 Using compose file: $COMPOSE_FILE"

# Check if compose file exists
if [ ! -f "$COMPOSE_FILE" ]; then
    echo "❌ Compose file not found: $COMPOSE_FILE"
    exit 1
fi

# Load environment variables
if [ -f ".env.${ENVIRONMENT}" ]; then
    echo "🔧 Loading environment variables from .env.${ENVIRONMENT}"
    export $(grep -v '^#' .env.${ENVIRONMENT} | xargs)
fi

# Pull latest images
echo "📥 Pulling latest images..."
docker-compose -f $COMPOSE_FILE pull

# Stop existing containers
echo "🛑 Stopping existing containers..."
docker-compose -f $COMPOSE_FILE down

# Start services
echo "🐳 Starting services..."
docker-compose -f $COMPOSE_FILE up -d

# Wait for database
echo "⏳ Waiting for database to be ready..."
sleep 10

# Run migrations
echo "🗄️ Running database migrations..."
docker-compose -f $COMPOSE_FILE exec app php bin/console doctrine:migrations:migrate --no-interaction

# Clear cache
echo "🧹 Clearing application cache..."
docker-compose -f $COMPOSE_FILE exec app php bin/console cache:clear --env=prod

echo "✅ Deployment complete!"
echo "🔍 Check logs with: docker-compose -f $COMPOSE_FILE logs -f"
echo "🌐 Application should be available at configured domain"
```

This Infrastructure as Code documentation provides ready-to-use configurations for deploying your Symfony application across different platforms and environments.