#!/bin/bash

# Code Quality Check Script
# Run PHP_CodeSniffer and PSALM on the codebase

set -e

echo "🔍 Running Code Quality Checks..."
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print section headers
print_section() {
    echo -e "${BLUE}▶ $1${NC}"
    echo "----------------------------------------"
}

# Run PHP_CodeSniffer
print_section "PHP_CodeSniffer (Code Style)"
if docker compose exec app ./vendor/bin/phpcs --standard=phpcs.xml src/ tests/; then
    echo -e "${GREEN}✓ PHP_CodeSniffer passed${NC}"
else
    echo -e "${RED}✗ PHP_CodeSniffer failed${NC}"
    echo ""
    echo "To auto-fix issues, run:"
    echo "  docker compose exec app ./vendor/bin/phpcbf --standard=phpcs.xml src/ tests/"
    exit 1
fi

echo ""

# Run PSALM
print_section "PSALM (Static Analysis)"
if docker compose exec app ./vendor/bin/psalm --show-info=false; then
    echo -e "${GREEN}✓ PSALM passed${NC}"
else
    echo -e "${RED}✗ PSALM failed${NC}"
    echo ""
    echo "Fix the static analysis issues manually"
    exit 1
fi

echo ""

# Run PHPUnit with coverage
print_section "PHPUnit Tests with Coverage"
if docker compose exec app ./vendor/bin/phpunit --coverage-text --coverage-html var/coverage/html; then
    echo -e "${GREEN}✓ All tests passed${NC}"
else
    echo -e "${RED}✗ Tests failed${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}🎉 All code quality checks passed!${NC}"