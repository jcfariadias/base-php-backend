<?php

require_once 'vendor/autoload.php';

use App\Domain\User\ValueObject\UserStatus;

// TDD RED phase - Test all expected behaviors for UserStatus
echo "=== Running UserStatus TDD Tests (RED phase) ===\n\n";

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

// Test 1: Should have all required enum cases
runTest("Should have all required enum cases", function() {
    $cases = UserStatus::cases();
    $expectedCases = ['ACTIVE', 'INACTIVE', 'PENDING', 'SUSPENDED', 'DELETED'];
    
    $actualCases = array_map(fn($case) => $case->name, $cases);
    
    foreach ($expectedCases as $expected) {
        if (!in_array($expected, $actualCases)) {
            throw new Exception("Missing enum case: $expected");
        }
    }
    
    if (count($cases) !== count($expectedCases)) {
        throw new Exception("Unexpected number of enum cases. Expected: " . count($expectedCases) . ", got: " . count($cases));
    }
});

// Test 2: Should create from valid string values
runTest("Should create from valid string values", function() {
    $validStatuses = [
        'active' => UserStatus::ACTIVE,
        'inactive' => UserStatus::INACTIVE,
        'pending' => UserStatus::PENDING,
        'suspended' => UserStatus::SUSPENDED,
        'deleted' => UserStatus::DELETED,
    ];
    
    foreach ($validStatuses as $string => $expectedCase) {
        $status = UserStatus::fromString($string);
        
        if ($status !== $expectedCase) {
            throw new Exception("fromString('$string') should return $expectedCase->name");
        }
    }
});

// Test 3: Should reject invalid string values
runTest("Should reject invalid string values", function() {
    $invalidStatuses = [
        'invalid',
        'ACTIVE', // case sensitive
        'enabled',
        'disabled',
        '',
        'unknown',
    ];
    
    foreach ($invalidStatuses as $invalidStatus) {
        try {
            UserStatus::fromString($invalidStatus);
            throw new Exception("Should have thrown exception for invalid status: '$invalidStatus'");
        } catch (InvalidArgumentException $e) {
            if (!str_contains($e->getMessage(), 'Invalid user status')) {
                throw new Exception("Wrong exception message for '$invalidStatus': " . $e->getMessage());
            }
        }
    }
});

// Test 4: Should implement toString correctly
runTest("Should implement toString correctly", function() {
    $status = UserStatus::ACTIVE;
    
    if ($status->toString() !== 'active') {
        throw new Exception("toString() should return 'active', got: " . $status->toString());
    }
    
    // Note: PHP 8.1 enums don't support __toString(), so we test the enum's native string conversion
    if ($status->value !== 'active') {
        throw new Exception("Enum value should be 'active', got: " . $status->value);
    }
});

// Test 5: Should implement equals correctly
runTest("Should implement equals correctly", function() {
    $status1 = UserStatus::ACTIVE;
    $status2 = UserStatus::fromString('active');
    $status3 = UserStatus::INACTIVE;
    
    if (!$status1->equals($status2)) {
        throw new Exception("Equal statuses should be equal");
    }
    
    if ($status1->equals($status3)) {
        throw new Exception("Different statuses should not be equal");
    }
});

// Test 6: Should implement business logic methods correctly
runTest("Should implement business logic methods correctly", function() {
    // Test isActive
    if (!UserStatus::ACTIVE->isActive()) {
        throw new Exception("ACTIVE should return true for isActive()");
    }
    
    if (UserStatus::INACTIVE->isActive()) {
        throw new Exception("INACTIVE should return false for isActive()");
    }
    
    // Test isInactive
    if (!UserStatus::INACTIVE->isInactive()) {
        throw new Exception("INACTIVE should return true for isInactive()");
    }
    
    if (UserStatus::ACTIVE->isInactive()) {
        throw new Exception("ACTIVE should return false for isInactive()");
    }
    
    // Test isPending
    if (!UserStatus::PENDING->isPending()) {
        throw new Exception("PENDING should return true for isPending()");
    }
    
    if (UserStatus::ACTIVE->isPending()) {
        throw new Exception("ACTIVE should return false for isPending()");
    }
    
    // Test isSuspended
    if (!UserStatus::SUSPENDED->isSuspended()) {
        throw new Exception("SUSPENDED should return true for isSuspended()");
    }
    
    if (UserStatus::ACTIVE->isSuspended()) {
        throw new Exception("ACTIVE should return false for isSuspended()");
    }
    
    // Test isDeleted
    if (!UserStatus::DELETED->isDeleted()) {
        throw new Exception("DELETED should return true for isDeleted()");
    }
    
    if (UserStatus::ACTIVE->isDeleted()) {
        throw new Exception("ACTIVE should return false for isDeleted()");
    }
});

// Test 7: Should implement canLogin correctly
runTest("Should implement canLogin correctly", function() {
    if (!UserStatus::ACTIVE->canLogin()) {
        throw new Exception("ACTIVE should allow login");
    }
    
    $nonLoginStatuses = [UserStatus::INACTIVE, UserStatus::PENDING, UserStatus::SUSPENDED, UserStatus::DELETED];
    
    foreach ($nonLoginStatuses as $status) {
        if ($status->canLogin()) {
            throw new Exception("$status->name should not allow login");
        }
    }
});

// Test 8: Should implement canBeActivated correctly
runTest("Should implement canBeActivated correctly", function() {
    $activatableStatuses = [UserStatus::INACTIVE, UserStatus::PENDING, UserStatus::SUSPENDED];
    
    foreach ($activatableStatuses as $status) {
        if (!$status->canBeActivated()) {
            throw new Exception("$status->name should be activatable");
        }
    }
    
    $nonActivatableStatuses = [UserStatus::ACTIVE, UserStatus::DELETED];
    
    foreach ($nonActivatableStatuses as $status) {
        if ($status->canBeActivated()) {
            throw new Exception("$status->name should not be activatable");
        }
    }
});

// Test 9: Should implement canBeDeactivated correctly
runTest("Should implement canBeDeactivated correctly", function() {
    if (!UserStatus::ACTIVE->canBeDeactivated()) {
        throw new Exception("ACTIVE should be deactivatable");
    }
    
    $nonDeactivatableStatuses = [UserStatus::INACTIVE, UserStatus::PENDING, UserStatus::SUSPENDED, UserStatus::DELETED];
    
    foreach ($nonDeactivatableStatuses as $status) {
        if ($status->canBeDeactivated()) {
            throw new Exception("$status->name should not be deactivatable");
        }
    }
});

// Test 10: Should be immutable (enum characteristics)
runTest("Should be immutable (enum characteristics)", function() {
    $status = UserStatus::ACTIVE;
    
    // Enums are naturally immutable, test that we can't modify internal state
    $reflection = new ReflectionClass(UserStatus::class);
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    
    foreach ($methods as $method) {
        $methodName = $method->getName();
        if (str_starts_with($methodName, 'set')) {
            throw new Exception("Found setter method: $methodName");
        }
    }
});

// Test 11: Should handle edge cases for business logic
runTest("Should handle edge cases for business logic", function() {
    // Test that each status has expected behavior patterns
    $statuses = [
        ['status' => UserStatus::ACTIVE, 'expectations' => [
            'canLogin' => true,
            'canBeActivated' => false,
            'canBeDeactivated' => true,
        ]],
        ['status' => UserStatus::INACTIVE, 'expectations' => [
            'canLogin' => false,
            'canBeActivated' => true,
            'canBeDeactivated' => false,
        ]],
        ['status' => UserStatus::PENDING, 'expectations' => [
            'canLogin' => false,
            'canBeActivated' => true,
            'canBeDeactivated' => false,
        ]],
        ['status' => UserStatus::SUSPENDED, 'expectations' => [
            'canLogin' => false,
            'canBeActivated' => true,
            'canBeDeactivated' => false,
        ]],
        ['status' => UserStatus::DELETED, 'expectations' => [
            'canLogin' => false,
            'canBeActivated' => false,
            'canBeDeactivated' => false,
        ]],
    ];
    
    foreach ($statuses as $item) {
        $status = $item['status'];
        $expected = $item['expectations'];
        foreach ($expected as $method => $expectedResult) {
            $actualResult = $status->$method();
            if ($actualResult !== $expectedResult) {
                throw new Exception("$status->name->$method() should return " . var_export($expectedResult, true) . ", got " . var_export($actualResult, true));
            }
        }
    }
});

// Test 12: Should implement value object contract
runTest("Should implement value object contract", function() {
    $status = UserStatus::ACTIVE;
    
    if (!method_exists($status, 'toString')) {
        throw new Exception("Missing toString method");
    }
    
    if (!method_exists($status, 'equals')) {
        throw new Exception("Missing equals method");
    }
    
    // Note: PHP 8.1 enums don't support __toString(), so we skip this check
    
    if (!is_string($status->toString())) {
        throw new Exception("toString should return string");
    }
    
    if (!is_bool($status->equals($status))) {
        throw new Exception("equals should return bool");
    }
    
    // Check business logic methods
    $businessMethods = ['isActive', 'isInactive', 'isPending', 'isSuspended', 'isDeleted', 'canLogin', 'canBeActivated', 'canBeDeactivated'];
    
    foreach ($businessMethods as $method) {
        if (!method_exists($status, $method)) {
            throw new Exception("Missing business logic method: $method");
        }
        
        if (!is_bool($status->$method())) {
            throw new Exception("$method should return bool");
        }
    }
});

echo "\n=== Test Results ===\n";
echo "Passed: $testsPassed\n";
echo "Failed: $testsFailed\n";
echo "Total: " . ($testsPassed + $testsFailed) . "\n";

if ($testsFailed === 0) {
    echo "\n🎉 All tests passed! UserStatus implementation meets TDD requirements.\n";
} else {
    echo "\n❌ Some tests failed. Implementation needs fixes.\n";
}