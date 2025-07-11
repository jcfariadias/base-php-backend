# JWT Configuration Documentation

This document describes the JWT (JSON Web Token) configuration for the authentication system.

## Overview

The application uses the [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle) to handle JWT authentication for API endpoints.

## Configuration Files

### 1. Environment Variables

#### Development (`.env` and `.env.local`)
```bash
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=test
```

#### Testing (`.env.test`)
```bash
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=test
```

#### Production (`.env.prod.example`)
```bash
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your-strong-jwt-passphrase-here-change-this
```

### 2. Bundle Configuration (`config/packages/lexik_jwt_authentication.yaml`)

```yaml
lexik_jwt_authentication:
    secret_key: '%env(resolve:JWT_SECRET_KEY)%'
    public_key: '%env(resolve:JWT_PUBLIC_KEY)%'
    pass_phrase: '%env(JWT_PASSPHRASE)%'
    token_ttl: 3600 # Token lifetime in seconds (1 hour)
    user_id_claim: email # Field from user entity to identify user in JWT
    token_extractors:
        authorization_header:
            enabled: true
            prefix: Bearer
            name: Authorization
        cookie:
            enabled: false
        query_parameter:
            enabled: false
            name: token
```

## Key Files

### RSA Key Pair
- **Private Key**: `config/jwt/private.pem`
- **Public Key**: `config/jwt/public.pem`

### Key Generation
The keys were generated using OpenSSL:
```bash
# Generate private key
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pass pass:test

# Generate public key from private key
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem -passin pass:test
```

## Security Configuration

### Symfony Security (`config/packages/security.yaml`)

```yaml
security:
    providers:
        app_user_provider:
            entity:
                class: App\Domain\User\Entity\User
                property: email
    
    firewalls:
        api_login:
            pattern: ^/api/auth/(login|register|logout|refresh)
            stateless: true
            security: false
        api:
            pattern: ^/api
            stateless: true
            provider: app_user_provider
            jwt: ~
    
    access_control:
        - { path: ^/api/auth/(login|register|logout|refresh), roles: PUBLIC_ACCESS }
        - { path: ^/api, roles: ROLE_USER }
```

## JWT Event Listener

### JWTCreatedListener (`src/Infrastructure/User/EventListener/JWTCreatedListener.php`)

This listener ensures proper JWT payload format:
```php
public function onJWTCreated(JWTCreatedEvent $event): void
{
    $user = $event->getUser();
    
    if (!$user instanceof User) {
        return;
    }

    $payload = $event->getData();
    
    // Ensure the email is properly set as a string
    $payload['email'] = $user->getEmail()->toString();
    
    // Add user ID for better token identification
    $payload['user_id'] = $user->getId()->toString();
    
    $event->setData($payload);
}
```

## Token Structure

### JWT Payload Example
```json
{
  "iat": 1752227550,
  "exp": 1752231150,
  "roles": ["ROLE_USER"],
  "email": "user@example.com",
  "user_id": "a53ba0c2-b92a-4733-a01f-83236c8c488e"
}
```

## API Endpoints

### Protected Endpoints
- `GET /api/auth/me` - Get current user information
- All endpoints under `/api/*` (except login/register/logout/refresh)

### Public Endpoints
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `POST /api/auth/logout` - User logout
- `POST /api/auth/refresh` - Token refresh

## Token Usage

### Authorization Header
```http
Authorization: Bearer <jwt_token>
```

### Example Request
```bash
curl -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..." \
     http://localhost/api/auth/me
```

## Security Best Practices

### For Production

1. **Strong Passphrase**: Use a strong, unique passphrase for JWT keys
2. **Key Security**: Protect private key files with proper file permissions
3. **CORS Configuration**: Restrict CORS to specific domains
4. **HTTPS Only**: Always use HTTPS in production
5. **Token TTL**: Set appropriate token lifetime (default: 1 hour)
6. **Key Rotation**: Implement key rotation strategy for long-term security

### Environment-Specific Settings

- **Development**: Use simple passphrase for convenience
- **Testing**: Use in-memory database and simplified configuration
- **Production**: Use strong passwords, restricted CORS, and HTTPS

## Troubleshooting

### Common Issues

1. **Invalid JWT signature**: Check that keys and passphrase match
2. **Token not found**: Verify Authorization header format
3. **User not authenticated**: Ensure user exists and token is valid
4. **Permission denied**: Check that user has required roles

### Debug Commands

```bash
# Test JWT key with passphrase
openssl rsa -in config/jwt/private.pem -passin pass:test -noout

# Run functional tests
docker-compose exec app composer test:functional
```

## References

- [LexikJWTAuthenticationBundle Documentation](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/3.x/Resources/doc/index.rst)
- [Symfony Security Documentation](https://symfony.com/doc/current/security.html)
- [JWT RFC Specification](https://tools.ietf.org/html/rfc7519)