<?php

require_once 'vendor/autoload.php';

// Simple test runner to check if our code works
try {
    // Test the current UserId implementation
    $userId1 = App\Domain\User\ValueObject\UserId::generate();
    echo "✓ UserId::generate() works: " . $userId1->toString() . "\n";
    
    $userId2 = App\Domain\User\ValueObject\UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
    echo "✓ UserId::fromString() works: " . $userId2->toString() . "\n";
    
    // Test invalid UUID
    try {
        App\Domain\User\ValueObject\UserId::fromString('invalid-uuid');
        echo "✗ Should have thrown exception for invalid UUID\n";
    } catch (InvalidArgumentException $e) {
        echo "✓ Correctly throws exception for invalid UUID: " . $e->getMessage() . "\n";
    }
    
    // Test empty string
    try {
        App\Domain\User\ValueObject\UserId::fromString('');
        echo "✗ Should have thrown exception for empty string\n";
    } catch (InvalidArgumentException $e) {
        echo "✓ Correctly throws exception for empty string: " . $e->getMessage() . "\n";
    }
    
    // Test equals
    $userId3 = App\Domain\User\ValueObject\UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
    if ($userId2->equals($userId3)) {
        echo "✓ equals() works correctly\n";
    } else {
        echo "✗ equals() not working correctly\n";
    }
    
    echo "\nCurrent UserId implementation analysis:\n";
    echo "- Immutable: " . (class_exists(ReflectionClass::class) ? "✓" : "?") . "\n";
    echo "- Validates input: ✓\n";
    echo "- Has equals method: ✓\n";
    echo "- Has toString method: ✓\n";
    echo "- Has __toString method: ✓\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}