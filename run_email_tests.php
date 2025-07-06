<?php

require_once 'vendor/autoload.php';

use App\Domain\User\ValueObject\Email;

// TDD RED phase - Test all expected behaviors for Email
echo "=== Running Email TDD Tests (RED phase) ===\n\n";

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
    $reflection = new ReflectionClass(Email::class);
    $publicMethods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    
    foreach ($publicMethods as $method) {
        $methodName = $method->getName();
        if (str_starts_with($methodName, 'set')) {
            throw new Exception("Found setter method: $methodName");
        }
    }
});

// Test 2: Should create from valid email
runTest("Should create from valid email", function() {
    $validEmails = [
        'user@example.com',
        'test.email@domain.org',
        'user+tag@example.co.uk',
        'first.last@subdomain.domain.com',
    ];

    foreach ($validEmails as $email) {
        $emailObj = Email::fromString($email);
        
        if (!($emailObj instanceof Email)) {
            throw new Exception("fromString() should return Email instance for: $email");
        }
        
        if ($emailObj->toString() !== strtolower($email)) {
            throw new Exception("Email should be normalized to lowercase for: $email");
        }
    }
});

// Test 3: Should normalize to lowercase
runTest("Should normalize email to lowercase", function() {
    $mixedCaseEmail = 'User.Test@EXAMPLE.COM';
    $email = Email::fromString($mixedCaseEmail);
    
    if ($email->toString() !== 'user.test@example.com') {
        throw new Exception("Email should be normalized to lowercase");
    }
});

// Test 4: Should trim whitespace
runTest("Should trim whitespace", function() {
    $emailWithWhitespace = '  user@example.com  ';
    $email = Email::fromString($emailWithWhitespace);
    
    if ($email->toString() !== 'user@example.com') {
        throw new Exception("Email should trim whitespace");
    }
});

// Test 5: Should reject empty string
runTest("Should reject empty string", function() {
    try {
        Email::fromString('');
        throw new Exception("Should have thrown exception for empty string");
    } catch (InvalidArgumentException $e) {
        if (!str_contains($e->getMessage(), 'Email cannot be empty')) {
            throw new Exception("Wrong exception message: " . $e->getMessage());
        }
    }
});

// Test 6: Should reject whitespace-only string
runTest("Should reject whitespace-only string", function() {
    try {
        Email::fromString('   ');
        throw new Exception("Should have thrown exception for whitespace string");
    } catch (InvalidArgumentException $e) {
        if (!str_contains($e->getMessage(), 'Email cannot be empty')) {
            throw new Exception("Wrong exception message: " . $e->getMessage());
        }
    }
});

// Test 7: Should reject invalid email formats
runTest("Should reject invalid email formats", function() {
    $invalidEmails = [
        'invalid-email',
        '@example.com',
        'user@',
        'user..name@example.com',
        'user@example',
        'user@@example.com',
    ];
    
    foreach ($invalidEmails as $invalidEmail) {
        try {
            Email::fromString($invalidEmail);
            throw new Exception("Should have thrown exception for: $invalidEmail");
        } catch (InvalidArgumentException $e) {
            if (!str_contains($e->getMessage(), 'Invalid email format')) {
                throw new Exception("Wrong exception message for $invalidEmail: " . $e->getMessage());
            }
        }
    }
});

// Test 8: Should reject email too long
runTest("Should reject email too long", function() {
    $longLocalPart = str_repeat('a', 250);
    $longEmail = $longLocalPart . '@example.com';
    
    try {
        Email::fromString($longEmail);
        throw new Exception("Should have thrown exception for long email");
    } catch (InvalidArgumentException $e) {
        if (!str_contains($e->getMessage(), 'Email is too long')) {
            throw new Exception("Wrong exception message: " . $e->getMessage());
        }
    }
});

// Test 9: Should implement equals correctly
runTest("Should implement equals correctly", function() {
    $email1 = Email::fromString('user@example.com');
    $email2 = Email::fromString('user@example.com');
    $email3 = Email::fromString('different@example.com');
    
    if (!$email1->equals($email2)) {
        throw new Exception("Equal emails should be equal");
    }
    
    if ($email1->equals($email3)) {
        throw new Exception("Different emails should not be equal");
    }
});

// Test 10: Should handle case insensitive equals
runTest("Should handle case insensitive equals", function() {
    $lowerEmail = Email::fromString('user@example.com');
    $upperEmail = Email::fromString('USER@EXAMPLE.COM');
    
    if (!$lowerEmail->equals($upperEmail)) {
        throw new Exception("Case should not matter for email equality");
    }
});

// Test 11: Should extract domain correctly
runTest("Should extract domain correctly", function() {
    $email = Email::fromString('user@example.com');
    
    if ($email->getDomain() !== 'example.com') {
        throw new Exception("getDomain() should return 'example.com', got: " . $email->getDomain());
    }
});

// Test 12: Should extract local part correctly
runTest("Should extract local part correctly", function() {
    $email = Email::fromString('user@example.com');
    
    if ($email->getLocalPart() !== 'user') {
        throw new Exception("getLocalPart() should return 'user', got: " . $email->getLocalPart());
    }
});

// Test 13: Should implement toString correctly
runTest("Should implement toString correctly", function() {
    $emailString = 'user@example.com';
    $email = Email::fromString($emailString);
    
    if ($email->toString() !== $emailString) {
        throw new Exception("toString() should match original email");
    }
    
    if ((string) $email !== $emailString) {
        throw new Exception("__toString() should match original email");
    }
});

// Test 14: Should prevent cloning
runTest("Should prevent cloning for immutability", function() {
    $email = Email::fromString('user@example.com');
    
    try {
        clone $email;
        throw new Exception("Should have thrown exception when cloning");
    } catch (BadMethodCallException $e) {
        if (!str_contains($e->getMessage(), 'immutable and cannot be cloned')) {
            throw new Exception("Wrong exception message: " . $e->getMessage());
        }
    }
});

// Test 15: Should prevent unserialization
runTest("Should prevent unserialization", function() {
    $email = Email::fromString('user@example.com');
    
    try {
        $email->__wakeup();
        throw new Exception("Should have thrown exception when calling __wakeup");
    } catch (BadMethodCallException $e) {
        if (!str_contains($e->getMessage(), 'cannot be unserialized')) {
            throw new Exception("Wrong exception message: " . $e->getMessage());
        }
    }
});

// Test 16: Should implement value object contract
runTest("Should implement value object contract", function() {
    $email = Email::fromString('user@example.com');
    
    if (!method_exists($email, 'toString')) {
        throw new Exception("Missing toString method");
    }
    
    if (!method_exists($email, 'equals')) {
        throw new Exception("Missing equals method");
    }
    
    if (!method_exists($email, '__toString')) {
        throw new Exception("Missing __toString method");
    }
    
    if (!method_exists($email, 'getDomain')) {
        throw new Exception("Missing getDomain method");
    }
    
    if (!method_exists($email, 'getLocalPart')) {
        throw new Exception("Missing getLocalPart method");
    }
    
    if (!is_string($email->toString())) {
        throw new Exception("toString should return string");
    }
    
    if (!is_bool($email->equals($email))) {
        throw new Exception("equals should return bool");
    }
    
    if (!is_string($email->getDomain())) {
        throw new Exception("getDomain should return string");
    }
    
    if (!is_string($email->getLocalPart())) {
        throw new Exception("getLocalPart should return string");
    }
});

echo "\n=== Test Results ===\n";
echo "Passed: $testsPassed\n";
echo "Failed: $testsFailed\n";
echo "Total: " . ($testsPassed + $testsFailed) . "\n";

if ($testsFailed === 0) {
    echo "\n🎉 All tests passed! Email implementation meets TDD requirements.\n";
} else {
    echo "\n❌ Some tests failed. Implementation needs fixes.\n";
}