<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\ValueObject;

use PHPUnit\Framework\TestCase;

/**
 * Base test case for ValueObject tests with common assertion methods
 */
abstract class ValueObjectTestCase extends TestCase
{
    /**
     * Assert that a value object is immutable by checking it has no public setters
     */
    protected function assertImmutable(string $className): void
    {
        $reflection = new \ReflectionClass($className);
        
        $publicMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as $method) {
            $methodName = $method->getName();
            
            // Check no setter methods exist
            $this->assertStringNotStartsWith('set', $methodName, 
                "Value object {$className} should not have setter methods, found: {$methodName}");
        }
    }

    /**
     * Assert that a value object properly validates input in constructor
     */
    protected function assertValidatesInput(callable $constructor, array $invalidInputs): void
    {
        foreach ($invalidInputs as $invalidInput) {
            $this->expectException(\InvalidArgumentException::class);
            $constructor($invalidInput);
        }
    }

    /**
     * Assert that two value objects are equal
     */
    protected function assertValueObjectsEqual(object $first, object $second): void
    {
        $this->assertTrue($first->equals($second), 
            'Value objects should be equal');
        
        $this->assertEquals($first->toString(), $second->toString(),
            'Value objects toString() should match');
            
        $this->assertEquals((string) $first, (string) $second,
            'Value objects __toString() should match');
    }

    /**
     * Assert that two value objects are not equal
     */
    protected function assertValueObjectsNotEqual(object $first, object $second): void
    {
        $this->assertFalse($first->equals($second), 
            'Value objects should not be equal');
    }

    /**
     * Assert that a value object can be serialized and deserialized
     */
    protected function assertSerializable(object $valueObject): void
    {
        $serialized = serialize($valueObject);
        $this->assertNotEmpty($serialized, 'Value object should be serializable');
        
        $deserialized = unserialize($serialized);
        $this->assertInstanceOf(get_class($valueObject), $deserialized,
            'Deserialized object should be same type');
        
        $this->assertTrue($valueObject->equals($deserialized),
            'Deserialized object should equal original');
    }
}