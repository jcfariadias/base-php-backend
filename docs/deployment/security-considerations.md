# Security Considerations and Secrets Management

## 🔐 Security Overview

### Security Layers
1. **Application Security** - JWT, CORS, Input validation
2. **Infrastructure Security** - Network isolation, Access controls
3. **Data Security** - Encryption at rest and in transit
4. **Secrets Management** - Environment variables, Key management
5. **Monitoring Security** - Audit logs, Intrusion detection

## 🔑 Secrets Management

### Environment Variables Best Practices

#### Production Environment Variables Template
```bash
# File: .env.prod.template

# Application Security
APP_ENV=prod
APP_SECRET=<generate-random-32-char-string>
APP_DEBUG=false

# Database (use connection pooling in production)
DATABASE_URL="postgresql://user:password@host:5432/dbname?sslmode=require&connect_timeout=10"

# Redis (with AUTH)
REDIS_URL="redis://:password@host:6379/0"

# JWT Configuration
JWT_SECRET_KEY=/var/jwt/private.pem
JWT_PUBLIC_KEY=/var/jwt/public.pem
JWT_PASSPHRASE=<strong-passphrase-min-12-chars>
JWT_TOKEN_TTL=3600

# CORS - Restrict to your domains only
CORS_ALLOW_ORIGIN="^https://(yourdomain\.com|api\.yourdomain\.com)$"

# Email (if applicable)
MAILER_DSN="smtp://user:password@smtp.provider.com:587"

# Third-party APIs
EXTERNAL_API_KEY=<api-key>
EXTERNAL_API_SECRET=<api-secret>

# Monitoring
SENTRY_DSN=<sentry-dsn>
```

#### Secret Generation Scripts
```bash
#!/bin/bash
# File: scripts/generate-secrets.sh

echo "🔐 Generating production secrets"

# Generate APP_SECRET (32 characters)
APP_SECRET=$(openssl rand -hex 16)
echo "APP_SECRET=$APP_SECRET"

# Generate JWT Passphrase
JWT_PASSPHRASE=$(openssl rand -base64 32)
echo "JWT_PASSPHRASE=$JWT_PASSPHRASE"

# Generate database password
DB_PASSWORD=$(openssl rand -base64 24 | tr -d "=+/" | cut -c1-25)
echo "DB_PASSWORD=$DB_PASSWORD"

# Generate Redis password
REDIS_PASSWORD=$(openssl rand -base64 24 | tr -d "=+/" | cut -c1-25)
echo "REDIS_PASSWORD=$REDIS_PASSWORD"

echo "🔐 Secrets generated successfully"
echo "⚠️ Store these secrets securely and never commit them to version control"
```

### JWT Security Configuration

#### Generate Production JWT Keys
```bash
#!/bin/bash
# File: scripts/setup-jwt-keys.sh

JWT_DIR="config/jwt"
PASSPHRASE_FILE=".jwt_passphrase"

echo "🔐 Setting up JWT keys for production"

# Create JWT directory
mkdir -p $JWT_DIR

# Generate passphrase
if [ ! -f "$PASSPHRASE_FILE" ]; then
    openssl rand -base64 32 > $PASSPHRASE_FILE
    echo "🔑 Generated new JWT passphrase"
fi

PASSPHRASE=$(cat $PASSPHRASE_FILE)

# Generate private key with passphrase
openssl genpkey \
    -out $JWT_DIR/private.pem \
    -algorithm RSA \
    -pkeyopt rsa_keygen_bits:4096 \
    -aes256 \
    -pass pass:$PASSPHRASE

# Generate public key
openssl pkey \
    -in $JWT_DIR/private.pem \
    -out $JWT_DIR/public.pem \
    -pubout \
    -passin pass:$PASSPHRASE

# Set proper permissions
chmod 600 $JWT_DIR/private.pem
chmod 644 $JWT_DIR/public.pem

echo "✅ JWT keys generated successfully"
echo "🔒 Private key: $JWT_DIR/private.pem"
echo "🔓 Public key: $JWT_DIR/public.pem"
echo "⚠️ Keep the passphrase file secure: $PASSPHRASE_FILE"
```

### Platform-Specific Secrets Management

#### DigitalOcean App Platform
```yaml
# In app.yaml
envs:
- key: APP_SECRET
  value: ${APP_SECRET}  # Set in dashboard
  type: SECRET

- key: DATABASE_URL
  value: ${db.DATABASE_URL}  # Auto-generated

- key: JWT_PASSPHRASE
  value: ${JWT_PASSPHRASE}  # Set in dashboard
  type: SECRET

# Upload JWT keys via dashboard or volume mount
```

#### Google Cloud Secret Manager
```bash
# Create secrets in Google Cloud
gcloud secrets create app-secret --data-file=<(echo -n "$APP_SECRET")
gcloud secrets create jwt-passphrase --data-file=<(echo -n "$JWT_PASSPHRASE")
gcloud secrets create jwt-private-key --data-file=config/jwt/private.pem
gcloud secrets create jwt-public-key --data-file=config/jwt/public.pem

# Grant access to Cloud Run service
gcloud projects add-iam-policy-binding $PROJECT_ID \
    --member="serviceAccount:$SERVICE_ACCOUNT" \
    --role="roles/secretmanager.secretAccessor"
```

#### Kubernetes Secrets
```yaml
# File: deployment/kubernetes/secrets.yaml
apiVersion: v1
kind: Secret
metadata:
  name: warehouse-secrets
  namespace: warehouse
type: Opaque
data:
  app-secret: <base64-encoded-app-secret>
  jwt-passphrase: <base64-encoded-jwt-passphrase>
  database-password: <base64-encoded-db-password>
  redis-password: <base64-encoded-redis-password>

---
apiVersion: v1
kind: Secret
metadata:
  name: jwt-keys
  namespace: warehouse
type: Opaque
data:
  private.pem: <base64-encoded-private-key>
  public.pem: <base64-encoded-public-key>
```

#### AWS Secrets Manager
```bash
# Create secrets in AWS
aws secretsmanager create-secret \
    --name "warehouse/app-secret" \
    --description "Application secret key" \
    --secret-string "$APP_SECRET"

aws secretsmanager create-secret \
    --name "warehouse/jwt-passphrase" \
    --description "JWT passphrase" \
    --secret-string "$JWT_PASSPHRASE"

# Create policy for ECS task role
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "secretsmanager:GetSecretValue"
      ],
      "Resource": [
        "arn:aws:secretsmanager:region:account:secret:warehouse/*"
      ]
    }
  ]
}
```

## 🛡️ Infrastructure Security

### Network Security

#### Docker Network Isolation
```yaml
# File: docker-compose.production.yml
version: '3.8'

services:
  app:
    networks:
      - app-tier
      - db-tier

  nginx:
    networks:
      - app-tier
      - web-tier
    ports:
      - "80:80"
      - "443:443"

  db:
    networks:
      - db-tier
    # No external ports exposed

  redis:
    networks:
      - db-tier
    # No external ports exposed

networks:
  web-tier:
    driver: bridge
  app-tier:
    driver: bridge
  db-tier:
    driver: bridge
    internal: true  # No external access
```

#### Kubernetes Network Policies
```yaml
# File: deployment/kubernetes/network-policy.yaml
apiVersion: networking.k8s.io/v1
kind: NetworkPolicy
metadata:
  name: warehouse-network-policy
  namespace: warehouse
spec:
  podSelector:
    matchLabels:
      app: warehouse
  policyTypes:
  - Ingress
  - Egress
  ingress:
  - from:
    - podSelector:
        matchLabels:
          app: nginx-ingress
    ports:
    - protocol: TCP
      port: 8080
  egress:
  - to:
    - podSelector:
        matchLabels:
          app: postgres
    ports:
    - protocol: TCP
      port: 5432
  - to:
    - podSelector:
        matchLabels:
          app: redis
    ports:
    - protocol: TCP
      port: 6379
```

### SSL/TLS Configuration

#### Nginx SSL Configuration
```nginx
# File: nginx/ssl.conf
server {
    listen 443 ssl http2;
    server_name your-domain.com;

    # SSL Configuration
    ssl_certificate /etc/ssl/certs/your-domain.crt;
    ssl_certificate_key /etc/ssl/private/your-domain.key;
    
    # Security headers
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # HSTS
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    
    # Security headers
    add_header X-Frame-Options DENY always;
    add_header X-Content-Type-Options nosniff always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Content-Security-Policy "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src 'self'; font-src 'self'; object-src 'none'; media-src 'self'; frame-src 'none';" always;

    location / {
        proxy_pass http://app:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}
```

### Database Security

#### PostgreSQL Security Configuration
```sql
-- File: database/security.sql

-- Create application user with limited privileges
CREATE USER warehouse_app WITH PASSWORD 'secure_password';

-- Grant only necessary permissions
GRANT CONNECT ON DATABASE warehouse TO warehouse_app;
GRANT USAGE ON SCHEMA public TO warehouse_app;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO warehouse_app;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO warehouse_app;

-- Set default privileges for future tables
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO warehouse_app;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT USAGE, SELECT ON SEQUENCES TO warehouse_app;

-- Enable SSL
ALTER SYSTEM SET ssl = on;
ALTER SYSTEM SET ssl_cert_file = '/var/lib/postgresql/server.crt';
ALTER SYSTEM SET ssl_key_file = '/var/lib/postgresql/server.key';

-- Configure authentication
-- In pg_hba.conf:
-- hostssl all all 0.0.0.0/0 md5
```

#### Redis Security Configuration
```redis
# File: redis/redis.conf

# Authentication
requirepass your_secure_redis_password

# Network security
bind 127.0.0.1 ::1
protected-mode yes
port 0
unixsocket /var/run/redis/redis-server.sock
unixsocketperm 700

# TLS (if using Redis 6+)
tls-port 6380
tls-cert-file /etc/ssl/certs/redis.crt
tls-key-file /etc/ssl/private/redis.key
tls-ca-cert-file /etc/ssl/certs/ca.crt

# Disable dangerous commands
rename-command FLUSHDB ""
rename-command FLUSHALL ""
rename-command KEYS ""
rename-command CONFIG "CONFIG_b835f8e7-9b4c-4f89-8c6a-1b2d3e4f5a6b"

# Logging
logfile /var/log/redis/redis-server.log
loglevel notice
```

## 🔍 Security Monitoring

### Security Event Logging
```php
<?php
// File: src/EventListener/SecurityListener.php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class SecurityListener implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $securityLogger
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            InteractiveLoginEvent::class => 'onLoginSuccess',
            LoginFailureEvent::class => 'onLoginFailure',
            RequestEvent::class => 'onRequest',
        ];
    }

    public function onLoginSuccess(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        $request = $event->getRequest();

        $this->securityLogger->info('User login successful', [
            'user_id' => $user->getId(),
            'username' => $user->getUserIdentifier(),
            'ip_address' => $request->getClientIp(),
            'user_agent' => $request->headers->get('User-Agent'),
            'timestamp' => new \DateTime(),
        ]);
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $request = $event->getRequest();
        
        $this->securityLogger->warning('Login attempt failed', [
            'username' => $request->request->get('username'),
            'ip_address' => $request->getClientIp(),
            'user_agent' => $request->headers->get('User-Agent'),
            'timestamp' => new \DateTime(),
        ]);
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        
        // Log suspicious requests
        if ($this->isSuspiciousRequest($request)) {
            $this->securityLogger->warning('Suspicious request detected', [
                'url' => $request->getUri(),
                'method' => $request->getMethod(),
                'ip_address' => $request->getClientIp(),
                'user_agent' => $request->headers->get('User-Agent'),
                'timestamp' => new \DateTime(),
            ]);
        }
    }

    private function isSuspiciousRequest($request): bool
    {
        $suspiciousPatterns = [
            '/\.\./i',           // Directory traversal
            '/union.*select/i',  // SQL injection
            '/<script/i',        // XSS attempts
            '/eval\(/i',         // Code injection
        ];

        $uri = $request->getUri();
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $uri)) {
                return true;
            }
        }

        return false;
    }
}
```

### Rate Limiting
```php
<?php
// File: src/EventListener/RateLimitListener.php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class RateLimitListener implements EventSubscriberInterface
{
    public function __construct(
        private RateLimiterFactory $anonymousApiLimiter,
        private RateLimiterFactory $authenticatedApiLimiter
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 100],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        
        // Only apply rate limiting to API routes
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $clientIp = $request->getClientIp();
        $limiter = $this->anonymousApiLimiter->create($clientIp);
        
        $limit = $limiter->consume(1);
        
        if (!$limit->isAccepted()) {
            $event->setResponse(new JsonResponse([
                'error' => 'Rate limit exceeded',
                'retry_after' => $limit->getRetryAfter()->getTimestamp()
            ], Response::HTTP_TOO_MANY_REQUESTS));
        }
    }
}
```

## 🔧 Security Hardening Checklist

### Application Level
- [ ] Configure strong JWT secrets and keys
- [ ] Enable CORS with specific origins only
- [ ] Implement rate limiting on API endpoints
- [ ] Validate and sanitize all user inputs
- [ ] Use HTTPS everywhere (HSTS headers)
- [ ] Set security headers (CSP, X-Frame-Options, etc.)
- [ ] Implement proper session management
- [ ] Log security events and failed authentication attempts

### Infrastructure Level
- [ ] Use network isolation (private networks)
- [ ] Configure firewalls to restrict access
- [ ] Enable database SSL/TLS connections
- [ ] Use strong passwords for all services
- [ ] Regularly update base images and dependencies
- [ ] Implement backup encryption
- [ ] Set up intrusion detection
- [ ] Configure log aggregation and monitoring

### Deployment Level
- [ ] Use secrets management systems
- [ ] Avoid hardcoding credentials
- [ ] Implement least privilege access
- [ ] Regular security updates and patches
- [ ] Vulnerability scanning of container images
- [ ] Audit trail for all administrative actions
- [ ] Disaster recovery and incident response plans

### Monitoring and Alerting
- [ ] Set up alerts for failed authentication attempts
- [ ] Monitor for unusual traffic patterns
- [ ] Track application errors and exceptions
- [ ] Monitor database connection attempts
- [ ] Set up security event correlation
- [ ] Regular security audits and penetration testing

This security guide provides comprehensive protection for your Symfony application across all deployment environments while maintaining practical usability.