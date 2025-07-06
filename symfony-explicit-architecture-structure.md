# Symfony Project with Explicit Architecture

## Folder Structure Design

This document outlines a Symfony project structure that implements Explicit Architecture principles, combining Domain-Driven Design, Hexagonal Architecture, and Symfony best practices.

```
symfony-project/
├── assets/                              # Frontend assets (Symfony convention)
│   ├── styles/
│   ├── scripts/
│   └── images/
├── bin/                                 # Executable scripts (Symfony convention)
│   └── console
├── config/                              # Configuration (Symfony convention)
│   ├── packages/
│   ├── routes/
│   ├── services.yaml
│   └── bundles.php
├── migrations/                          # Database migrations (Symfony convention)
├── public/                              # Web root (Symfony convention)
│   ├── index.php
│   └── build/
├── src/                                 # Application source code
│   ├── Kernel.php                       # Symfony Kernel
│   │
│   ├── SharedKernel/                    # Shared Kernel (cross-component functionality)
│   │   ├── Domain/
│   │   │   ├── Event/
│   │   │   │   ├── DomainEvent.php
│   │   │   │   └── ApplicationEvent.php
│   │   │   ├── ValueObject/
│   │   │   │   ├── Id.php
│   │   │   │   ├── Email.php
│   │   │   │   └── Money.php
│   │   │   └── Exception/
│   │   │       ├── DomainException.php
│   │   │       └── ValidationException.php
│   │   ├── Application/
│   │   │   ├── Bus/
│   │   │   │   ├── Command/
│   │   │   │   │   ├── CommandBus.php
│   │   │   │   │   └── Command.php
│   │   │   │   ├── Query/
│   │   │   │   │   ├── QueryBus.php
│   │   │   │   │   └── Query.php
│   │   │   │   └── Event/
│   │   │   │       ├── EventBus.php
│   │   │   │       └── EventDispatcher.php
│   │   │   └── DTO/
│   │   │       └── BaseDTO.php
│   │   └── Infrastructure/
│   │       ├── Persistence/
│   │       │   ├── Repository/
│   │       │   │   └── BaseRepository.php
│   │       │   └── Specification/
│   │       │       └── Specification.php
│   │       └── Messaging/
│   │           ├── MessageBus.php
│   │           └── AsyncMessageHandler.php
│   │
│   ├── UserManagement/                  # COMPONENT: User Management (Bounded Context)
│   │   ├── Domain/                      # Domain Layer
│   │   │   ├── Entity/
│   │   │   │   ├── User.php
│   │   │   │   ├── Role.php
│   │   │   │   └── Permission.php
│   │   │   ├── ValueObject/
│   │   │   │   ├── UserId.php
│   │   │   │   ├── Username.php
│   │   │   │   └── Password.php
│   │   │   ├── Service/
│   │   │   │   ├── UserDomainService.php
│   │   │   │   └── PasswordHashingService.php
│   │   │   ├── Event/
│   │   │   │   ├── UserRegistered.php
│   │   │   │   ├── UserActivated.php
│   │   │   │   └── PasswordChanged.php
│   │   │   ├── Exception/
│   │   │   │   ├── UserNotFoundException.php
│   │   │   │   └── InvalidCredentialsException.php
│   │   │   └── Repository/              # Domain Repository Interfaces (Ports)
│   │   │       ├── UserRepositoryInterface.php
│   │   │       └── RoleRepositoryInterface.php
│   │   │
│   │   ├── Application/                 # Application Layer
│   │   │   ├── UseCase/                 # Application Services / Use Cases
│   │   │   │   ├── RegisterUser/
│   │   │   │   │   ├── RegisterUserCommand.php
│   │   │   │   │   ├── RegisterUserHandler.php
│   │   │   │   │   └── RegisterUserResponse.php
│   │   │   │   ├── AuthenticateUser/
│   │   │   │   │   ├── AuthenticateUserCommand.php
│   │   │   │   │   ├── AuthenticateUserHandler.php
│   │   │   │   │   └── AuthenticationResponse.php
│   │   │   │   └── GetUserProfile/
│   │   │   │       ├── GetUserProfileQuery.php
│   │   │   │       ├── GetUserProfileHandler.php
│   │   │   │       └── UserProfileResponse.php
│   │   │   ├── Port/                    # Application Ports (Interfaces)
│   │   │   │   ├── Input/               # Primary Ports (Driving)
│   │   │   │   │   ├── UserManagementFacade.php
│   │   │   │   │   └── AuthenticationService.php
│   │   │   │   └── Output/              # Secondary Ports (Driven)
│   │   │   │       ├── UserNotificationPort.php
│   │   │   │       ├── EmailSenderPort.php
│   │   │   │       └── AuditLogPort.php
│   │   │   ├── EventHandler/
│   │   │   │   ├── UserRegisteredHandler.php
│   │   │   │   └── SendWelcomeEmailHandler.php
│   │   │   └── DTO/
│   │   │       ├── UserDTO.php
│   │   │       ├── CreateUserDTO.php
│   │   │       └── UserCredentialsDTO.php
│   │   │
│   │   └── Infrastructure/              # Infrastructure Layer
│   │       ├── Persistence/             # Secondary Adapters (Driven)
│   │       │   ├── Doctrine/
│   │       │   │   ├── Repository/
│   │       │   │   │   ├── DoctrineUserRepository.php
│   │       │   │   │   └── DoctrineRoleRepository.php
│   │       │   │   ├── Entity/          # Doctrine Entity Mappings
│   │       │   │   │   ├── UserEntity.php
│   │       │   │   │   └── RoleEntity.php
│   │       │   │   └── Type/
│   │       │   │       ├── UserIdType.php
│   │       │   │       └── EmailType.php
│   │       │   └── InMemory/            # For testing
│   │       │       ├── InMemoryUserRepository.php
│   │       │       └── InMemoryRoleRepository.php
│   │       ├── Adapter/                 # External Service Adapters
│   │       │   ├── Email/
│   │       │   │   ├── SymfonyMailerAdapter.php
│   │       │   │   └── SwiftMailerAdapter.php
│   │       │   ├── Notification/
│   │       │   │   ├── SlackNotificationAdapter.php
│   │       │   │   └── SmsNotificationAdapter.php
│   │       │   └── Audit/
│   │       │       ├── DatabaseAuditAdapter.php
│   │       │       └── FileAuditAdapter.php
│   │       └── Controller/              # Primary Adapters (Driving)
│   │           ├── Api/
│   │           │   ├── V1/
│   │           │   │   ├── UserController.php
│   │           │   │   ├── AuthController.php
│   │           │   │   └── ProfileController.php
│   │           │   └── V2/
│   │           │       └── UserController.php
│   │           ├── Web/
│   │           │   ├── UserController.php
│   │           │   ├── AuthController.php
│   │           │   └── ProfileController.php
│   │           └── Console/
│   │               ├── CreateUserCommand.php
│   │               └── ListUsersCommand.php
│   │
│   ├── ProductCatalog/                  # COMPONENT: Product Catalog (Bounded Context)
│   │   ├── Domain/
│   │   │   ├── Entity/
│   │   │   │   ├── Product.php
│   │   │   │   ├── Category.php
│   │   │   │   └── Price.php
│   │   │   ├── ValueObject/
│   │   │   │   ├── ProductId.php
│   │   │   │   ├── SKU.php
│   │   │   │   ├── ProductName.php
│   │   │   │   └── Money.php
│   │   │   ├── Service/
│   │   │   │   ├── ProductDomainService.php
│   │   │   │   └── PricingService.php
│   │   │   ├── Event/
│   │   │   │   ├── ProductCreated.php
│   │   │   │   ├── ProductPriceChanged.php
│   │   │   │   └── ProductOutOfStock.php
│   │   │   └── Repository/
│   │   │       ├── ProductRepositoryInterface.php
│   │   │       └── CategoryRepositoryInterface.php
│   │   │
│   │   ├── Application/
│   │   │   ├── UseCase/
│   │   │   │   ├── CreateProduct/
│   │   │   │   │   ├── CreateProductCommand.php
│   │   │   │   │   ├── CreateProductHandler.php
│   │   │   │   │   └── CreateProductResponse.php
│   │   │   │   ├── GetProduct/
│   │   │   │   │   ├── GetProductQuery.php
│   │   │   │   │   ├── GetProductHandler.php
│   │   │   │   │   └── ProductDetailsResponse.php
│   │   │   │   └── UpdateProductPrice/
│   │   │   │       ├── UpdateProductPriceCommand.php
│   │   │   │       ├── UpdateProductPriceHandler.php
│   │   │   │       └── UpdateProductPriceResponse.php
│   │   │   ├── Port/
│   │   │   │   ├── Input/
│   │   │   │   │   ├── ProductManagementFacade.php
│   │   │   │   │   └── ProductCatalogService.php
│   │   │   │   └── Output/
│   │   │   │       ├── InventoryServicePort.php
│   │   │   │       ├── PriceCalculatorPort.php
│   │   │   │       └── ProductSearchPort.php
│   │   │   ├── EventHandler/
│   │   │   │   ├── ProductCreatedHandler.php
│   │   │   │   └── UpdateInventoryHandler.php
│   │   │   └── DTO/
│   │   │       ├── ProductDTO.php
│   │   │       ├── CreateProductDTO.php
│   │   │       └── ProductSummaryDTO.php
│   │   │
│   │   └── Infrastructure/
│   │       ├── Persistence/
│   │       │   ├── Doctrine/
│   │       │   │   ├── Repository/
│   │       │   │   │   ├── DoctrineProductRepository.php
│   │       │   │   │   └── DoctrineCategoryRepository.php
│   │       │   │   └── Entity/
│   │       │   │       ├── ProductEntity.php
│   │       │   │       └── CategoryEntity.php
│   │       │   └── Elasticsearch/
│   │       │       └── ElasticsearchProductRepository.php
│   │       ├── Adapter/
│   │       │   ├── Inventory/
│   │       │   │   └── RestInventoryServiceAdapter.php
│   │       │   ├── Pricing/
│   │       │   │   └── ExternalPricingServiceAdapter.php
│   │       │   └── Search/
│   │       │       ├── ElasticsearchAdapter.php
│   │       │       └── SolrAdapter.php
│   │       └── Controller/
│   │           ├── Api/
│   │           │   └── V1/
│   │           │       ├── ProductController.php
│   │           │       └── CategoryController.php
│   │           ├── Web/
│   │           │   ├── ProductController.php
│   │           │   └── CatalogController.php
│   │           └── Console/
│   │               ├── ImportProductsCommand.php
│   │               └── ReindexProductsCommand.php
│   │
│   ├── OrderManagement/                 # COMPONENT: Order Management (Bounded Context)
│   │   ├── Domain/
│   │   │   ├── Entity/
│   │   │   │   ├── Order.php
│   │   │   │   ├── OrderItem.php
│   │   │   │   └── Customer.php
│   │   │   ├── ValueObject/
│   │   │   │   ├── OrderId.php
│   │   │   │   ├── OrderNumber.php
│   │   │   │   ├── OrderStatus.php
│   │   │   │   └── ShippingAddress.php
│   │   │   ├── Service/
│   │   │   │   ├── OrderDomainService.php
│   │   │   │   ├── OrderTotalCalculator.php
│   │   │   │   └── ShippingCalculator.php
│   │   │   ├── Event/
│   │   │   │   ├── OrderPlaced.php
│   │   │   │   ├── OrderShipped.php
│   │   │   │   └── OrderCancelled.php
│   │   │   └── Repository/
│   │   │       ├── OrderRepositoryInterface.php
│   │   │       └── CustomerRepositoryInterface.php
│   │   │
│   │   ├── Application/
│   │   │   ├── UseCase/
│   │   │   │   ├── PlaceOrder/
│   │   │   │   │   ├── PlaceOrderCommand.php
│   │   │   │   │   ├── PlaceOrderHandler.php
│   │   │   │   │   └── PlaceOrderResponse.php
│   │   │   │   ├── GetOrderDetails/
│   │   │   │   │   ├── GetOrderDetailsQuery.php
│   │   │   │   │   ├── GetOrderDetailsHandler.php
│   │   │   │   │   └── OrderDetailsResponse.php
│   │   │   │   └── CancelOrder/
│   │   │   │       ├── CancelOrderCommand.php
│   │   │   │       ├── CancelOrderHandler.php
│   │   │   │       └── CancelOrderResponse.php
│   │   │   ├── Port/
│   │   │   │   ├── Input/
│   │   │   │   │   ├── OrderManagementFacade.php
│   │   │   │   │   └── OrderProcessingService.php
│   │   │   │   └── Output/
│   │   │   │       ├── PaymentServicePort.php
│   │   │   │       ├── InventoryServicePort.php
│   │   │   │       └── ShippingServicePort.php
│   │   │   ├── EventHandler/
│   │   │   │   ├── OrderPlacedHandler.php
│   │   │   │   ├── ProcessPaymentHandler.php
│   │   │   │   └── UpdateInventoryHandler.php
│   │   │   └── DTO/
│   │   │       ├── OrderDTO.php
│   │   │       ├── CreateOrderDTO.php
│   │   │       └── OrderSummaryDTO.php
│   │   │
│   │   └── Infrastructure/
│   │       ├── Persistence/
│   │       │   └── Doctrine/
│   │       │       ├── Repository/
│   │       │       │   ├── DoctrineOrderRepository.php
│   │       │       │   └── DoctrineCustomerRepository.php
│   │       │       └── Entity/
│   │       │           ├── OrderEntity.php
│   │       │           ├── OrderItemEntity.php
│   │       │           └── CustomerEntity.php
│   │       ├── Adapter/
│   │       │   ├── Payment/
│   │       │   │   ├── StripePaymentAdapter.php
│   │       │   │   └── PayPalPaymentAdapter.php
│   │       │   ├── Inventory/
│   │       │   │   └── RestInventoryServiceAdapter.php
│   │       │   └── Shipping/
│   │       │       ├── DHLShippingAdapter.php
│   │       │       └── FedExShippingAdapter.php
│   │       └── Controller/
│   │           ├── Api/
│   │           │   └── V1/
│   │           │       ├── OrderController.php
│   │           │       └── CheckoutController.php
│   │           ├── Web/
│   │           │   ├── OrderController.php
│   │           │   └── CheckoutController.php
│   │           └── Console/
│   │               ├── ProcessOrdersCommand.php
│   │               └── OrderReportCommand.php
│   │
│   └── Common/                          # Common Infrastructure (Symfony-specific)
│       ├── EventListener/
│       │   ├── ExceptionListener.php
│       │   ├── SecurityListener.php
│       │   └── RequestListener.php
│       ├── Security/
│       │   ├── Authenticator/
│       │   │   ├── JwtAuthenticator.php
│       │   │   └── ApiKeyAuthenticator.php
│       │   ├── Voter/
│       │   │   ├── UserVoter.php
│       │   │   └── OrderVoter.php
│       │   └── Provider/
│       │       └── UserProvider.php
│       ├── Serializer/
│       │   ├── Normalizer/
│       │   │   ├── UserNormalizer.php
│       │   │   └── OrderNormalizer.php
│       │   └── Encoder/
│       │       └── JsonEncoder.php
│       ├── Validator/
│       │   ├── Constraint/
│       │   │   ├── UniqueEmail.php
│       │   │   └── ValidSKU.php
│       │   └── ConstraintValidator/
│       │       ├── UniqueEmailValidator.php
│       │       └── ValidSKUValidator.php
│       └── Form/
│           ├── Type/
│           │   ├── UserType.php
│           │   ├── ProductType.php
│           │   └── OrderType.php
│           └── Extension/
│               └── HelpTypeExtension.php
├── templates/                           # Twig templates (Symfony convention)
│   ├── base.html.twig
│   ├── user/
│   │   ├── profile.html.twig
│   │   └── login.html.twig
│   ├── product/
│   │   ├── catalog.html.twig
│   │   └── detail.html.twig
│   └── order/
│       ├── checkout.html.twig
│       └── confirmation.html.twig
├── tests/                               # Test files
│   ├── Unit/                           # Unit tests by component
│   │   ├── UserManagement/
│   │   │   ├── Domain/
│   │   │   │   ├── Entity/
│   │   │   │   │   └── UserTest.php
│   │   │   │   └── Service/
│   │   │   │       └── UserDomainServiceTest.php
│   │   │   ├── Application/
│   │   │   │   └── UseCase/
│   │   │   │       └── RegisterUser/
│   │   │   │           └── RegisterUserHandlerTest.php
│   │   │   └── Infrastructure/
│   │   │       └── Persistence/
│   │   │           └── DoctrineUserRepositoryTest.php
│   │   ├── ProductCatalog/
│   │   └── OrderManagement/
│   ├── Integration/                    # Integration tests
│   │   ├── UserManagement/
│   │   │   └── UserRegistrationTest.php
│   │   ├── ProductCatalog/
│   │   └── OrderManagement/
│   ├── Functional/                     # End-to-end tests
│   │   ├── Api/
│   │   │   ├── UserApiTest.php
│   │   │   ├── ProductApiTest.php
│   │   │   └── OrderApiTest.php
│   │   └── Web/
│   │       ├── UserWebTest.php
│   │       ├── ProductWebTest.php
│   │       └── OrderWebTest.php
│   └── Support/                        # Test utilities
│       ├── Factory/
│       │   ├── UserFactory.php
│       │   ├── ProductFactory.php
│       │   └── OrderFactory.php
│       └── Builder/
│           ├── UserBuilder.php
│           ├── ProductBuilder.php
│           └── OrderBuilder.php
├── translations/                        # Translation files (Symfony convention)
│   ├── messages.en.yaml
│   ├── messages.pt.yaml
│   └── validators.en.yaml
├── var/                                # Runtime files (Symfony convention)
│   ├── cache/
│   ├── log/
│   └── sessions/
├── vendor/                             # Composer dependencies (Symfony convention)
├── .env                               # Environment variables (Symfony convention)
├── .env.local                         # Local environment overrides
├── composer.json                      # PHP dependencies
├── composer.lock                      # Locked dependency versions
├── symfony.lock                       # Symfony Flex lock file
└── README.md
```

## Architecture Mapping

### Explicit Architecture Implementation

#### 1. **Three Fundamental Blocks**

- **User Interface Layer**: 
  - `*/Infrastructure/Controller/` - Primary Adapters (Web, API, Console)
  - `templates/` - Twig templates for web interface
  - `assets/` - Frontend assets

- **Application Core**:
  - `*/Domain/` - Domain Layer (Entities, Value Objects, Domain Services)
  - `*/Application/` - Application Layer (Use Cases, Application Services)
  - `SharedKernel/` - Common domain functionality

- **Infrastructure Layer**:
  - `*/Infrastructure/Adapter/` - Secondary Adapters (External services)
  - `*/Infrastructure/Persistence/` - Data persistence adapters
  - `Common/` - Symfony-specific infrastructure

#### 2. **Ports & Adapters Pattern**

- **Ports (Interfaces)**:
  - `*/Domain/Repository/` - Domain repository interfaces
  - `*/Application/Port/Input/` - Primary ports (driving)
  - `*/Application/Port/Output/` - Secondary ports (driven)

- **Primary Adapters (Driving)**:
  - `*/Infrastructure/Controller/` - Web, API, and Console controllers
  - Adapt external requests to application use cases

- **Secondary Adapters (Driven)**:
  - `*/Infrastructure/Persistence/` - Database adapters
  - `*/Infrastructure/Adapter/` - External service adapters
  - Implement output ports defined by application

#### 3. **Application Core Organization**

- **Application Layer**:
  - `*/Application/UseCase/` - Command/Query handlers (use cases)
  - `*/Application/EventHandler/` - Application event handlers
  - `*/Application/DTO/` - Data transfer objects
  - `SharedKernel/Application/Bus/` - Command/Query/Event buses

- **Domain Layer**:
  - `*/Domain/Entity/` - Domain entities with business logic
  - `*/Domain/ValueObject/` - Immutable value objects
  - `*/Domain/Service/` - Domain services for cross-entity logic
  - `*/Domain/Event/` - Domain events
  - `SharedKernel/Domain/` - Shared domain concepts

#### 4. **Component Organization (Package by Component)**

Each bounded context (UserManagement, ProductCatalog, OrderManagement) is organized as a complete component with:
- Its own domain model
- Application services
- Infrastructure adapters
- Clear boundaries and interfaces

#### 5. **Dependency Direction**

All dependencies point inward toward the Application Core:
- Controllers depend on Application services
- Application services depend on Domain entities and repository interfaces
- Infrastructure adapters implement domain interfaces
- No domain code depends on infrastructure

#### 6. **Shared Kernel**

Common functionality shared across components:
- Base domain events and exceptions
- Common value objects (Id, Email, Money)
- Command/Query/Event bus infrastructure
- Base repository patterns

## Benefits of This Structure

1. **Clear Separation of Concerns**: Each layer has distinct responsibilities
2. **Testability**: Easy to unit test domain logic in isolation
3. **Flexibility**: Can swap infrastructure implementations without affecting business logic
4. **Maintainability**: Changes are localized to specific components
5. **Symfony Integration**: Leverages Symfony conventions while maintaining architectural integrity
6. **Scalability**: Components can evolve independently
7. **Team Development**: Different teams can work on different components

## Implementation Notes

- Use Symfony's service container for dependency injection
- Configure services to respect architectural boundaries
- Use Symfony Messenger for command/query/event buses
- Implement Doctrine repositories as infrastructure adapters
- Use Symfony Form component for input validation in controllers
- Leverage Symfony's event system for application events