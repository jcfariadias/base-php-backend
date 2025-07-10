#!/bin/bash

# Auto-fix Code Style Script
# Use PHP_CodeSniffer's phpcbf to automatically fix code style issues

set -e

echo "🔧 Auto-fixing code style issues..."

# Run PHP_CodeSniffer Code Beautifier and Fixer
if docker compose exec app ./vendor/bin/phpcbf --standard=phpcs.xml src/ tests/; then
    echo "✓ Code style issues fixed"
else
    echo "ℹ️  No fixable issues found or some issues require manual fixing"
fi

echo ""
echo "🔍 Running PHP_CodeSniffer to check remaining issues..."

# Check if there are any remaining issues
if docker compose exec app ./vendor/bin/phpcs --standard=phpcs.xml src/ tests/; then
    echo "✓ All code style issues resolved!"
else
    echo "⚠️  Some issues require manual fixing"
    exit 1
fi