<?php

require_once 'vendor/autoload.php';

use App\Domain\User\ValueObject\UserRole;

// TDD RED phase - Test all expected behaviors for UserRole
echo "=== Running UserRole TDD Tests (RED phase) ===\n\n";

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
    $cases = UserRole::cases();
    $expectedCases = ['ADMIN', 'USER', 'MANAGER', 'TENANT_ADMIN', 'TENANT_USER'];
    
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
    $validRoles = [
        'ROLE_ADMIN' => UserRole::ADMIN,
        'ROLE_USER' => UserRole::USER,
        'ROLE_MANAGER' => UserRole::MANAGER,
        'ROLE_TENANT_ADMIN' => UserRole::TENANT_ADMIN,
        'ROLE_TENANT_USER' => UserRole::TENANT_USER,
    ];
    
    foreach ($validRoles as $string => $expectedCase) {
        $role = UserRole::fromString($string);
        
        if ($role !== $expectedCase) {
            throw new Exception("fromString('$string') should return $expectedCase->name");
        }
    }
});

// Test 3: Should reject invalid string values
runTest("Should reject invalid string values", function() {
    $invalidRoles = [
        'invalid',
        'admin', // case sensitive
        'ROLE_SUPER_ADMIN',
        'USER',
        '',
        'unknown',
        'ROLE_INVALID',
    ];
    
    foreach ($invalidRoles as $invalidRole) {
        try {
            UserRole::fromString($invalidRole);
            throw new Exception("Should have thrown exception for invalid role: '$invalidRole'");
        } catch (InvalidArgumentException $e) {
            if (!str_contains($e->getMessage(), 'Invalid user role')) {
                throw new Exception("Wrong exception message for '$invalidRole': " . $e->getMessage());
            }
        }
    }
});

// Test 4: Should implement toString correctly
runTest("Should implement toString correctly", function() {
    $role = UserRole::ADMIN;
    
    if ($role->toString() !== 'ROLE_ADMIN') {
        throw new Exception("toString() should return 'ROLE_ADMIN', got: " . $role->toString());
    }
    
    // Note: PHP 8.1 enums don't support __toString(), so we test the enum's native string conversion
    if ($role->value !== 'ROLE_ADMIN') {
        throw new Exception("Enum value should be 'ROLE_ADMIN', got: " . $role->value);
    }
});

// Test 5: Should implement equals correctly
runTest("Should implement equals correctly", function() {
    $role1 = UserRole::ADMIN;
    $role2 = UserRole::fromString('ROLE_ADMIN');
    $role3 = UserRole::USER;
    
    if (!$role1->equals($role2)) {
        throw new Exception("Equal roles should be equal");
    }
    
    if ($role1->equals($role3)) {
        throw new Exception("Different roles should not be equal");
    }
});

// Test 6: Should implement role identification methods correctly
runTest("Should implement role identification methods correctly", function() {
    // Test isAdmin
    if (!UserRole::ADMIN->isAdmin()) {
        throw new Exception("ADMIN should return true for isAdmin()");
    }
    
    if (UserRole::USER->isAdmin()) {
        throw new Exception("USER should return false for isAdmin()");
    }
    
    // Test isUser
    if (!UserRole::USER->isUser()) {
        throw new Exception("USER should return true for isUser()");
    }
    
    if (UserRole::ADMIN->isUser()) {
        throw new Exception("ADMIN should return false for isUser()");
    }
    
    // Test isManager
    if (!UserRole::MANAGER->isManager()) {
        throw new Exception("MANAGER should return true for isManager()");
    }
    
    if (UserRole::USER->isManager()) {
        throw new Exception("USER should return false for isManager()");
    }
    
    // Test isTenantAdmin
    if (!UserRole::TENANT_ADMIN->isTenantAdmin()) {
        throw new Exception("TENANT_ADMIN should return true for isTenantAdmin()");
    }
    
    if (UserRole::ADMIN->isTenantAdmin()) {
        throw new Exception("ADMIN should return false for isTenantAdmin()");
    }
    
    // Test isTenantUser
    if (!UserRole::TENANT_USER->isTenantUser()) {
        throw new Exception("TENANT_USER should return true for isTenantUser()");
    }
    
    if (UserRole::USER->isTenantUser()) {
        throw new Exception("USER should return false for isTenantUser()");
    }
});

// Test 7: Should implement privilege checking correctly
runTest("Should implement privilege checking correctly", function() {
    // Test hasAdminPrivileges
    $adminRoles = [UserRole::ADMIN, UserRole::TENANT_ADMIN];
    foreach ($adminRoles as $role) {
        if (!$role->hasAdminPrivileges()) {
            throw new Exception("$role->name should have admin privileges");
        }
    }
    
    $nonAdminRoles = [UserRole::USER, UserRole::MANAGER, UserRole::TENANT_USER];
    foreach ($nonAdminRoles as $role) {
        if ($role->hasAdminPrivileges()) {
            throw new Exception("$role->name should not have admin privileges");
        }
    }
    
    // Test hasManagerPrivileges
    $managerRoles = [UserRole::ADMIN, UserRole::MANAGER, UserRole::TENANT_ADMIN];
    foreach ($managerRoles as $role) {
        if (!$role->hasManagerPrivileges()) {
            throw new Exception("$role->name should have manager privileges");
        }
    }
    
    $nonManagerRoles = [UserRole::USER, UserRole::TENANT_USER];
    foreach ($nonManagerRoles as $role) {
        if ($role->hasManagerPrivileges()) {
            throw new Exception("$role->name should not have manager privileges");
        }
    }
    
    // Test canManageTenant
    $tenantManagerRoles = [UserRole::ADMIN, UserRole::TENANT_ADMIN];
    foreach ($tenantManagerRoles as $role) {
        if (!$role->canManageTenant()) {
            throw new Exception("$role->name should be able to manage tenant");
        }
    }
    
    $nonTenantManagerRoles = [UserRole::USER, UserRole::MANAGER, UserRole::TENANT_USER];
    foreach ($nonTenantManagerRoles as $role) {
        if ($role->canManageTenant()) {
            throw new Exception("$role->name should not be able to manage tenant");
        }
    }
});

// Test 8: Should implement role hierarchy correctly
runTest("Should implement role hierarchy correctly", function() {
    $hierarchyTests = [
        ['role' => UserRole::ADMIN, 'expectedLevel' => 5],
        ['role' => UserRole::TENANT_ADMIN, 'expectedLevel' => 4],
        ['role' => UserRole::MANAGER, 'expectedLevel' => 3],
        ['role' => UserRole::TENANT_USER, 'expectedLevel' => 2],
        ['role' => UserRole::USER, 'expectedLevel' => 1],
    ];
    
    foreach ($hierarchyTests as $test) {
        $role = $test['role'];
        $expectedLevel = $test['expectedLevel'];
        if ($role->getHierarchyLevel() !== $expectedLevel) {
            throw new Exception("$role->name should have hierarchy level $expectedLevel, got: " . $role->getHierarchyLevel());
        }
    }
});

// Test 9: Should implement canAccessRole correctly
runTest("Should implement canAccessRole correctly", function() {
    // ADMIN should access all roles
    $admin = UserRole::ADMIN;
    foreach (UserRole::cases() as $role) {
        if (!$admin->canAccessRole($role)) {
            throw new Exception("ADMIN should be able to access $role->name");
        }
    }
    
    // USER should only access USER
    $user = UserRole::USER;
    if (!$user->canAccessRole(UserRole::USER)) {
        throw new Exception("USER should be able to access USER");
    }
    
    if ($user->canAccessRole(UserRole::ADMIN)) {
        throw new Exception("USER should not be able to access ADMIN");
    }
    
    // MANAGER should access USER, TENANT_USER, and MANAGER
    $manager = UserRole::MANAGER;
    $accessibleByManager = [UserRole::USER, UserRole::TENANT_USER, UserRole::MANAGER];
    foreach ($accessibleByManager as $role) {
        if (!$manager->canAccessRole($role)) {
            throw new Exception("MANAGER should be able to access $role->name");
        }
    }
    
    if ($manager->canAccessRole(UserRole::ADMIN)) {
        throw new Exception("MANAGER should not be able to access ADMIN");
    }
});

// Test 10: Should be immutable (enum characteristics)
runTest("Should be immutable (enum characteristics)", function() {
    $role = UserRole::ADMIN;
    
    // Enums are naturally immutable, test that we can't modify internal state
    $reflection = new ReflectionClass(UserRole::class);
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    
    foreach ($methods as $method) {
        $methodName = $method->getName();
        if (str_starts_with($methodName, 'set')) {
            throw new Exception("Found setter method: $methodName");
        }
    }
});

// Test 11: Should handle edge cases for role hierarchy
runTest("Should handle edge cases for role hierarchy", function() {
    // Test that hierarchy levels are consistent with privilege checks
    $roles = UserRole::cases();
    
    foreach ($roles as $role1) {
        foreach ($roles as $role2) {
            $canAccess = $role1->canAccessRole($role2);
            $higherOrEqual = $role1->getHierarchyLevel() >= $role2->getHierarchyLevel();
            
            if ($canAccess !== $higherOrEqual) {
                throw new Exception("canAccessRole and getHierarchyLevel are inconsistent for $role1->name vs $role2->name");
            }
        }
    }
});

// Test 12: Should implement value object contract
runTest("Should implement value object contract", function() {
    $role = UserRole::ADMIN;
    
    if (!method_exists($role, 'toString')) {
        throw new Exception("Missing toString method");
    }
    
    if (!method_exists($role, 'equals')) {
        throw new Exception("Missing equals method");
    }
    
    // Note: PHP 8.1 enums don't support __toString(), so we skip this check
    
    if (!is_string($role->toString())) {
        throw new Exception("toString should return string");
    }
    
    if (!is_bool($role->equals($role))) {
        throw new Exception("equals should return bool");
    }
    
    // Check role identification methods
    $identificationMethods = ['isAdmin', 'isUser', 'isManager', 'isTenantAdmin', 'isTenantUser'];
    
    foreach ($identificationMethods as $method) {
        if (!method_exists($role, $method)) {
            throw new Exception("Missing role identification method: $method");
        }
        
        if (!is_bool($role->$method())) {
            throw new Exception("$method should return bool");
        }
    }
    
    // Check privilege methods
    $privilegeMethods = ['hasAdminPrivileges', 'hasManagerPrivileges', 'canManageTenant'];
    
    foreach ($privilegeMethods as $method) {
        if (!method_exists($role, $method)) {
            throw new Exception("Missing privilege method: $method");
        }
        
        if (!is_bool($role->$method())) {
            throw new Exception("$method should return bool");
        }
    }
    
    // Check hierarchy methods
    if (!method_exists($role, 'getHierarchyLevel')) {
        throw new Exception("Missing getHierarchyLevel method");
    }
    
    if (!is_int($role->getHierarchyLevel())) {
        throw new Exception("getHierarchyLevel should return int");
    }
    
    if (!method_exists($role, 'canAccessRole')) {
        throw new Exception("Missing canAccessRole method");
    }
    
    if (!is_bool($role->canAccessRole($role))) {
        throw new Exception("canAccessRole should return bool");
    }
});

echo "\n=== Test Results ===\n";
echo "Passed: $testsPassed\n";
echo "Failed: $testsFailed\n";
echo "Total: " . ($testsPassed + $testsFailed) . "\n";

if ($testsFailed === 0) {
    echo "\n🎉 All tests passed! UserRole implementation meets TDD requirements.\n";
} else {
    echo "\n❌ Some tests failed. Implementation needs fixes.\n";
}