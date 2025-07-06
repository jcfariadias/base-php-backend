# AuthController TDD Refactoring Plan

## Overview
Refactor the AuthController to follow gold standard rules using TDD principles. Split the current multi-method AuthController into 5 separate single-responsibility controllers, each following the gold standard pattern with proper validation and serialization.

## GOLD STANDARD VIOLATIONS FOUND
1. AuthController has multiple methods - should be single method controllers
2. Controllers should only have __invoke method
3. Each controller handles ONE specific action
4. Controllers should use Symfony serializer and validator
5. DTOs should be validated with Symfony validation annotations

## Current State Analysis
**Current AuthController has:**
- login() method
- logout() method  
- refresh() method
- register() method
- me() method

**Should be split into:**
1. **LoginController** - handles user authentication
2. **LogoutController** - handles user logout
3. **RefreshTokenController** - handles token refresh
4. **RegisterUserController** - handles user registration
5. **GetUserProfileController** - handles user profile retrieval

## Gold Standard Controller Pattern
Each controller should follow this exact pattern:
```php
class SomeController extends AbstractController
{
    public function __construct(
        private readonly SomeUseCase $useCase,
        private readonly ValidatorInterface $validator,
        private readonly SerializerInterface $serializer
    ) {}

    #[Route('/some-path', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        // 1. Deserialize request into DTO
        // 2. Validate DTO
        // 3. Call UseCase
        // 4. Return JsonResponse
    }
}
```

## TDD Process Requirements
1. **RED**: Write failing tests for each controller FIRST
2. **GREEN**: Create controller to pass tests
3. **REFACTOR**: Optimize while maintaining tests
4. **VALIDATION**: All DTOs must use Symfony validation annotations
5. **CONTAINER**: Run all commands in Docker container

## Tasks

### 1. Setup Testing Infrastructure
- [ ] Create controller test directory structure under tests/Unit/Interfaces/Http/User/Controller/
- [ ] Create integration test directory under tests/Integration/
- [ ] Setup PHPUnit configuration for controllers
- [ ] Create base controller test class with common setup
- [ ] Setup test doubles and mocking utilities

### 2. Create Enhanced DTOs with Validation
- [ ] **RED**: Write tests for LoginDTO with validation annotations
- [ ] **GREEN**: Create LoginDTO with Symfony validation constraints
- [ ] **RED**: Write tests for LogoutDTO (if needed)
- [ ] **GREEN**: Create LogoutDTO with validation
- [ ] **RED**: Write tests for RefreshTokenDTO with validation
- [ ] **GREEN**: Create RefreshTokenDTO with validation constraints
- [ ] **RED**: Write tests for RegisterUserDTO with validation
- [ ] **GREEN**: Create RegisterUserDTO with comprehensive validation
- [ ] **RED**: Write tests for GetUserProfileDTO (if needed)
- [ ] **GREEN**: Create GetUserProfileDTO with validation

### 3. LoginController TDD Implementation
- [ ] **RED**: Write failing tests for LoginController
  - [ ] Test __invoke() with valid credentials
  - [ ] Test __invoke() with invalid credentials
  - [ ] Test __invoke() with malformed request
  - [ ] Test __invoke() with validation errors
  - [ ] Test dependency injection works
  - [ ] Test serializer integration
  - [ ] Test validator integration
  - [ ] Test proper HTTP status codes
  - [ ] Test error response format
- [ ] **GREEN**: Create LoginController extending AbstractController
  - [ ] Implement __invoke method only
  - [ ] Constructor with UseCase, Validator, Serializer injection
  - [ ] Route annotation for /api/auth/login POST
  - [ ] Deserialize request into LoginDTO
  - [ ] Validate DTO using Symfony validator
  - [ ] Call LoginUseCase
  - [ ] Return proper JsonResponse
- [ ] **REFACTOR**: Optimize implementation while maintaining tests

### 4. LogoutController TDD Implementation
- [ ] **RED**: Write failing tests for LogoutController
  - [ ] Test __invoke() returns success message
  - [ ] Test __invoke() handles authenticated users
  - [ ] Test __invoke() handles unauthenticated users
  - [ ] Test proper HTTP status codes
  - [ ] Test dependency injection
- [ ] **GREEN**: Create LogoutController extending AbstractController
  - [ ] Implement __invoke method only
  - [ ] Constructor with minimal dependencies
  - [ ] Route annotation for /api/auth/logout POST
  - [ ] Return proper logout response
- [ ] **REFACTOR**: Optimize implementation

### 5. RefreshTokenController TDD Implementation
- [ ] **RED**: Write failing tests for RefreshTokenController
  - [ ] Test __invoke() with valid refresh token
  - [ ] Test __invoke() with invalid refresh token
  - [ ] Test __invoke() with expired token
  - [ ] Test __invoke() with malformed request
  - [ ] Test __invoke() with validation errors
  - [ ] Test dependency injection
  - [ ] Test serializer and validator integration
  - [ ] Test proper HTTP status codes
- [ ] **GREEN**: Create RefreshTokenController extending AbstractController
  - [ ] Implement __invoke method only
  - [ ] Constructor with UseCase, Validator, Serializer injection
  - [ ] Route annotation for /api/auth/refresh POST
  - [ ] Deserialize request into RefreshTokenDTO
  - [ ] Validate DTO
  - [ ] Call RefreshTokenUseCase
  - [ ] Return proper JsonResponse
- [ ] **REFACTOR**: Optimize implementation

### 6. RegisterUserController TDD Implementation
- [ ] **RED**: Write failing tests for RegisterUserController
  - [ ] Test __invoke() with valid registration data
  - [ ] Test __invoke() with duplicate email
  - [ ] Test __invoke() with invalid email format
  - [ ] Test __invoke() with weak password
  - [ ] Test __invoke() with missing required fields
  - [ ] Test __invoke() with validation errors
  - [ ] Test dependency injection
  - [ ] Test serializer and validator integration
  - [ ] Test proper HTTP status codes (201 Created)
- [ ] **GREEN**: Create RegisterUserController extending AbstractController
  - [ ] Implement __invoke method only
  - [ ] Constructor with UseCase, Validator, Serializer injection
  - [ ] Route annotation for /api/auth/register POST
  - [ ] Deserialize request into RegisterUserDTO
  - [ ] Validate DTO with comprehensive rules
  - [ ] Call RegisterUseCase
  - [ ] Return proper JsonResponse with 201 status
- [ ] **REFACTOR**: Optimize implementation

### 7. GetUserProfileController TDD Implementation
- [ ] **RED**: Write failing tests for GetUserProfileController
  - [ ] Test __invoke() with authenticated user
  - [ ] Test __invoke() with unauthenticated user
  - [ ] Test __invoke() returns proper user data
  - [ ] Test dependency injection
  - [ ] Test serializer integration
  - [ ] Test proper HTTP status codes
- [ ] **GREEN**: Create GetUserProfileController extending AbstractController
  - [ ] Implement __invoke method only
  - [ ] Constructor with dependencies
  - [ ] Route annotation for /api/auth/me GET
  - [ ] Handle CurrentUser attribute
  - [ ] Return serialized user data
  - [ ] Handle authentication errors
- [ ] **REFACTOR**: Optimize implementation

### 8. DTO Validation Enhancement
- [ ] Add comprehensive Symfony validation annotations to DTOs:
  - [ ] Email validation (@Email)
  - [ ] Password strength validation (@Length, custom constraints)
  - [ ] Required field validation (@NotBlank)
  - [ ] Format validation (@Choice, @Range)
- [ ] Create custom validation constraints if needed
- [ ] Test all validation scenarios

### 9. Service Layer Integration
- [ ] Ensure all controllers use existing UseCases properly
- [ ] Test controller->UseCase->Service->Repository flow
- [ ] Verify dependency injection chain works
- [ ] Test error propagation from services to controllers

### 10. Route Configuration
- [ ] Remove old AuthController routes
- [ ] Configure new controller routes in routes.yaml
- [ ] Ensure proper route priorities
- [ ] Test route resolution

### 11. Integration Testing
- [ ] Create integration tests for complete auth flows
- [ ] Test all controllers work with real HTTP requests
- [ ] Test authentication/authorization flows
- [ ] Verify JWT token handling works correctly
- [ ] Test database integration

### 12. Container and Dependency Injection
- [ ] Update services.yaml for new controllers
- [ ] Configure serializer and validator services
- [ ] Test container can resolve all dependencies
- [ ] Verify autowiring works correctly

### 13. Error Handling Standardization
- [ ] Ensure all controllers return consistent error formats
- [ ] Test validation error responses
- [ ] Test domain exception handling
- [ ] Test HTTP status code consistency

### 14. Docker Container Testing
- [ ] Run all PHPUnit tests in container: `docker exec -it warehouse-space-app-1 bin/console --env=test`
- [ ] Run individual controller tests in container
- [ ] Verify container database access works
- [ ] Test container service resolution

### 15. Security and Validation Testing
- [ ] Test CSRF protection (if applicable)
- [ ] Test input sanitization
- [ ] Test SQL injection prevention
- [ ] Test XSS prevention
- [ ] Test rate limiting (if implemented)

### 16. Final Validation and Cleanup
- [ ] Delete old AuthController.php
- [ ] Run complete test suite in container
- [ ] Verify all tests pass
- [ ] Check test coverage metrics
- [ ] Code review for gold standard compliance

## Gold Standard Requirements Checklist

### Controller Requirements
- [ ] **Single Method**: Each controller has only __invoke method
- [ ] **Single Responsibility**: Each controller handles ONE action
- [ ] **Dependency Injection**: Constructor injection of UseCase, Validator, Serializer
- [ ] **AbstractController**: All extend AbstractController
- [ ] **Route Annotations**: Proper Route attributes with methods
- [ ] **JsonResponse**: All return JsonResponse
- [ ] **Error Handling**: Consistent error response format

### DTO Requirements  
- [ ] **Validation Annotations**: Symfony validation constraints
- [ ] **Immutable**: DTOs are read-only after construction
- [ ] **Type Safety**: Proper type hints and return types
- [ ] **Serializable**: Work with Symfony serializer

### Testing Requirements
- [ ] **TDD Cycle**: RED-GREEN-REFACTOR for every controller
- [ ] **100% Coverage**: All controller methods tested
- [ ] **Dependency Mocking**: All dependencies properly mocked
- [ ] **Integration Tests**: Real HTTP request/response testing
- [ ] **Container Tests**: Docker container execution

### Architecture Requirements
- [ ] **Separation of Concerns**: Controllers only handle HTTP concerns
- [ ] **Clean Dependencies**: Controller -> UseCase -> Service -> Repository
- [ ] **Interface Segregation**: Focused, cohesive interfaces
- [ ] **Error Boundaries**: Proper exception handling at each layer

## Progress Tracking
- **Total Tasks**: 62
- **Completed**: 0
- **In Progress**: 0
- **Remaining**: 62

## Commands to Execute (All in Docker Container)

### Test Commands
```bash
# Run all tests
docker exec -it warehouse-space-app-1 ./vendor/bin/phpunit

# Run specific controller tests
docker exec -it warehouse-space-app-1 ./vendor/bin/phpunit tests/Unit/Interfaces/Http/User/Controller/

# Run integration tests
docker exec -it warehouse-space-app-1 ./vendor/bin/phpunit tests/Integration/

# Check test coverage
docker exec -it warehouse-space-app-1 ./vendor/bin/phpunit --coverage-html coverage/
```

### Development Commands
```bash
# Clear cache
docker exec -it warehouse-space-app-1 bin/console cache:clear --env=test

# Debug container services
docker exec -it warehouse-space-app-1 bin/console debug:container

# Debug routes
docker exec -it warehouse-space-app-1 bin/console debug:router
```

## Notes
- **CRITICAL**: Follow strict TDD Red-Green-Refactor cycle
- **CRITICAL**: All commands must run in Docker container
- **CRITICAL**: Each controller must have only __invoke method
- **CRITICAL**: Use Symfony serializer and validator
- **CRITICAL**: DTOs must have validation annotations
- Focus on minimal changes to existing patterns
- Preserve existing naming conventions
- Use existing UseCase classes
- Test error conditions thoroughly
- Maintain backwards compatibility for API consumers

## Review Criteria
When complete, each controller should:
1. Have exactly one public method (__invoke)
2. Extend AbstractController
3. Use constructor dependency injection
4. Deserialize requests into validated DTOs
5. Call appropriate UseCase
6. Return consistent JsonResponse format
7. Have 100% test coverage
8. Pass all tests when run in Docker container