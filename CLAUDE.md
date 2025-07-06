# Instructions

## General guidelines

1. First, think about the problem, read the codebase to find relevant files, and write a plan in tasks/todo.md.
2. The plan should have a list of tasks that you can mark as completed.
3. Tasks should be as atomic as possible
4. Before starting work, contact me and I will review the plan.
5. Then, start working on the tasks, marking them as completed as you progress.
6. Please, at each step of the way, just give me a detailed explanation of the changes you made.
7. Make each task and code change as simple as possible. We want to avoid massive or complex changes. Each change should impact the code as minimally as possible. It all comes down to simplicity.
8. Finally, add a review section to the todo.md file with a summary of the changes made and any other relevant information.

## Development Guidelines

- Every task should be done in a TDD way
- Don't over engineer. KISS

## Feature Implementation System Guidelines

### Feature Implementation Priority Rules

- IMMEDIATE EXECUTION: Launch parallel Tasks immediately upon feature requests
- NO CLARIFICATION: Skip asking what type of implementation unless absolutely critical
- PARALLEL BY DEFAULT: Always use 3-parallel-Task method for efficiency

### Parallel Feature Implementation Workflow

1. **Models**: Create entity/model classes with database mappings
2. **Controllers**: Create API controllers with route handlers
3. **Services**: Create business logic and service layer classes
4. **Database**: Create migrations, seeders, and schema updates
5. **Validation**: Create request validation rules and form requests
6. **Tests**: Create unit tests, integration tests, and API tests
7. **Routes**: Define API routes and route groups
8. **Configuration**: Update environment variables, config files, and dependencies
9. **Documentation**: Update API documentation, OpenAPI specs, and README
10. **Review and Validation**: Run test suites, verify database migrations, check API endpoints, validate securityTentar novamenteClaude ainda não tem a capacidade de executar o código que gera.O Claude pode cometer erros. Confira sempre as respostas.

### Context Optimization Rules

- Strip out all comments when reading code files for analysis
- Each task handles ONLY specified files or file types
- Task 7 combines small config/doc updates to prevent over-splitting

### Feature Implementation Guidelines

- **CRITICAL**: Make MINIMAL CHANGES to existing patterns and structures
- **CRITICAL**: Preserve existing naming conventions and file organization
- Follow project's established architecture provided in `architecture.md` and component patterns
- Use existing utility functions and avoid duplicating functionality

## Development Workflow

- Every time a task is completed commit and push to repo

## Architectural Guidelines

- Always refer to and strictly follow the guidelines in `architecture.md`