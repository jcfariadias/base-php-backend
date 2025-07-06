# Task Completion Workflow

## Development Process
1. **Plan**: Update tasks/todo.md with specific implementation steps
2. **Implement**: Follow TDD approach (Test-Driven Development)
3. **Test**: Run all tests to ensure no regressions
4. **Commit**: Commit changes with descriptive messages
5. **Document**: Update relevant documentation

## Before Starting Work
1. Review the task in `tasks/todo.md`
2. Mark task as **In Progress** (`[x]`)
3. Plan implementation approach
4. Create or update tests first (TDD)

## During Development
1. Follow **single responsibility principle**
2. Keep changes **atomic and minimal**
3. Test continuously during development
4. Follow established patterns and conventions

## After Completion
1. Run complete test suite: `php bin/phpunit`
2. Check code style: `php-cs-fixer fix`
3. Verify database migrations: `php bin/console doctrine:migrations:migrate`
4. Test API endpoints manually or with tools
5. Mark task as **Completed** (`[x] ✓`)
6. Commit changes: `git add . && git commit -m "Task: descriptive message"`
7. Update review section in `tasks/todo.md`

## Quality Checks
- All tests pass
- Code follows PSR-12 standards
- Database migrations work correctly
- API endpoints respond correctly
- No security vulnerabilities introduced
- Documentation updated if needed

## Git Commit Guidelines
- Use descriptive commit messages
- Reference task ID if applicable
- Include brief description of changes
- Example: "Configure PostgreSQL 15+ and Redis caching infrastructure"