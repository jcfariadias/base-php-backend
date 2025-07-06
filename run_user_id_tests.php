<?php

require_once 'vendor/autoload.php';

use App\Domain\User\ValueObject\UserId;

// TDD RED phase - Test all expected behaviors
echo "=== Running UserId TDD Tests (RED phase) ===\n\n";

$testsPassed = 0;
$testsFailed = 0;

function runTest(string $testName, callable $test): void {
    global $testsPassed, $testsFailed;
    
    try {
        $test();
        echo "✓ $testName\n";
        $testsPassed++;
    } catch (Exception $e) {
        echo "✗ $testName: " . $e->getMessage() . "\n";
        $testsFailed++;
    } catch (Error $e) {
        echo "✗ $testName: " . $e->getMessage() . "\n";
        $testsFailed++;
    }
}

// Test 1: Should be immutable (no public setters)
runTest("Should be immutable (no public setters)", function() {
    $reflection = new ReflectionClass(UserId::class);
    $publicMethods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    
    foreach ($publicMethods as $method) {
        $methodName = $method->getName();
        if (str_starts_with($methodName, 'set')) {
            throw new Exception("Found setter method: $methodName");
        }
    }
});

// Test 2: Should generate valid UUID
runTest("Should generate valid UUID", function() {
    $userId = UserId::generate();
    
    if (!($userId instanceof UserId)) {
        throw new Exception("generate() should return UserId instance");
    }
    
    $uuidPattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    if (!preg_match($uuidPattern, $userId->toString())) {
        throw new Exception("Generated UUID doesn't match expected pattern");
    }
});

// Test 3: Should create from valid UUID string
runTest("Should create from valid UUID string", function() {
    $validUuid = '550e8400-e29b-41d4-a716-446655440000';
    $userId = UserId::fromString($validUuid);
    
    if ($userId->toString() !== $validUuid) {
        throw new Exception("fromString() should preserve UUID value");
    }
});

// Test 4: Should reject empty string
runTest("Should reject empty string", function() {
    try {
        UserId::fromString('');
        throw new Exception("Should have thrown exception for empty string");
    } catch (InvalidArgumentException $e) {
        if (!str_contains($e->getMessage(), 'User ID cannot be empty')) {
            throw new Exception("Wrong exception message: " . $e->getMessage());
        }
    }
});

// Test 5: Should reject whitespace-only string
runTest("Should reject whitespace-only string", function() {
    try {
        UserId::fromString('   ');
        throw new Exception("Should have thrown exception for whitespace string");
    } catch (InvalidArgumentException $e) {
        if (!str_contains($e->getMessage(), 'User ID cannot be empty')) {
            throw new Exception("Wrong exception message: " . $e->getMessage());
        }
    }
});

// Test 6: Should reject invalid UUID formats
runTest("Should reject invalid UUID formats", function() {
    $invalidUuids = [
        'invalid-uuid',
        '12345',
        'not-a-uuid-at-all',
        '550e8400-e29b-41d4-a716-44665544000',  // too short
        '550e8400-e29b-41d4-a716-4466554400000', // too long
        'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ', // invalid chars
    ];
    
    foreach ($invalidUuids as $invalidUuid) {
        try {
            UserId::fromString($invalidUuid);
            throw new Exception("Should have thrown exception for: $invalidUuid");
        } catch (InvalidArgumentException $e) {
            if (!str_contains($e->getMessage(), 'Invalid UUID format')) {
                throw new Exception("Wrong exception message for $invalidUuid: " . $e->getMessage());
            }
        }
    }
});

// Test 7: Should implement equals correctly
runTest("Should implement equals correctly", function() {
    $uuid = '550e8400-e29b-41d4-a716-446655440000';
    $userId1 = UserId::fromString($uuid);
    $userId2 = UserId::fromString($uuid);
    $userId3 = UserId::fromString('550e8400-e29b-41d4-a716-446655440001');
    
    if (!$userId1->equals($userId2)) {
        throw new Exception("Equal UUIDs should be equal");
    }
    
    if ($userId1->equals($userId3)) {
        throw new Exception("Different UUIDs should not be equal");
    }
});

// Test 8: Should handle case insensitive UUIDs
runTest("Should handle case insensitive UUIDs", function() {
    $lowerUuid = '550e8400-e29b-41d4-a716-446655440000';
    $upperUuid = '550E8400-E29B-41D4-A716-446655440000';
    
    $userId1 = UserId::fromString($lowerUuid);
    $userId2 = UserId::fromString($upperUuid);
    
    if (!$userId1->equals($userId2)) {
        throw new Exception("Case should not matter for UUID equality");
    }
});

// Test 9: Should implement toString correctly
runTest("Should implement toString correctly", function() {
    $uuid = '550e8400-e29b-41d4-a716-446655440000';
    $userId = UserId::fromString($uuid);
    
    if ($userId->toString() !== $uuid) {
        throw new Exception("toString() should match original UUID");
    }
    
    if ((string) $userId !== $uuid) {
        throw new Exception("__toString() should match original UUID");
    }
});

// Test 10: Should prevent cloning
runTest("Should prevent cloning for immutability", function() {
    $userId = UserId::generate();
    
    try {
        clone $userId;
        throw new Exception("Should have thrown exception when cloning");
    } catch (BadMethodCallException $e) {
        if (!str_contains($e->getMessage(), 'immutable and cannot be cloned')) {
            throw new Exception("Wrong exception message: " . $e->getMessage());
        }
    }
});

// Test 11: Should prevent unserialization
runTest("Should prevent unserialization", function() {
    $userId = UserId::generate();
    
    try {
        $userId->__wakeup();
        throw new Exception("Should have thrown exception when calling __wakeup");
    } catch (BadMethodCallException $e) {
        if (!str_contains($e->getMessage(), 'cannot be unserialized')) {
            throw new Exception("Wrong exception message: " . $e->getMessage());
        }
    }
});

// Test 12: Should generate unique IDs
runTest("Should generate unique IDs", function() {
    $userId1 = UserId::generate();
    $userId2 = UserId::generate();
    
    if ($userId1->toString() === $userId2->toString()) {
        throw new Exception("Generated UUIDs should be unique");
    }
    
    if ($userId1->equals($userId2)) {
        throw new Exception("Generated UUIDs should not be equal");
    }
});

// Test 13: Should implement value object contract
runTest("Should implement value object contract", function() {
    $userId = UserId::generate();
    
    if (!method_exists($userId, 'toString')) {
        throw new Exception("Missing toString method");
    }
    
    if (!method_exists($userId, 'equals')) {
        throw new Exception("Missing equals method");
    }
    
    if (!method_exists($userId, '__toString')) {
        throw new Exception("Missing __toString method");
    }
    
    if (!is_string($userId->toString())) {
        throw new Exception("toString should return string");
    }
    
    if (!is_bool($userId->equals($userId))) {
        throw new Exception("equals should return bool");
    }
});

echo "\n=== Test Results ===\n";
echo "Passed: $testsPassed\n";
echo "Failed: $testsFailed\n";
echo "Total: " . ($testsPassed + $testsFailed) . "\n";

if ($testsFailed === 0) {
    echo "\n🎉 All tests passed! UserId implementation meets TDD requirements.\n";
} else {
    echo "\n❌ Some tests failed. Implementation needs fixes.\n";
}