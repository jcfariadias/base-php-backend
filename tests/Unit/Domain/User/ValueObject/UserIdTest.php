<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\User\ValueObject;

use App\Domain\User\ValueObject\UserId;
use InvalidArgumentException;

/**
 * Test cases for UserId value object following TDD principles
 */
final class UserIdTest extends ValueObjectTestCase
{
    /**
     * @test
     */
    public function it_should_be_immutable(): void
    {
        $this->assertImmutable(UserId::class);
    }

    /**
     * @test
     */
    public function it_should_generate_valid_uuid(): void
    {
        $userId = UserId::generate();
        
        $this->assertInstanceOf(UserId::class, $userId);
        $this->assertIsString($userId->toString());
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $userId->toString()
        );
    }

    /**
     * @test
     */
    public function it_should_create_from_valid_uuid_string(): void
    {
        $validUuid = '550e8400-e29b-41d4-a716-446655440000';
        $userId = UserId::fromString($validUuid);
        
        $this->assertInstanceOf(UserId::class, $userId);
        $this->assertEquals($validUuid, $userId->toString());
    }

    /**
     * @test
     */
    public function it_should_reject_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User ID cannot be empty');
        
        UserId::fromString('');
    }

    /**
     * @test
     */
    public function it_should_reject_whitespace_only_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User ID cannot be empty');
        
        UserId::fromString('   ');
    }

    /**
     * @test
     */
    public function it_should_reject_invalid_uuid_format(): void
    {
        $invalidUuids = [
            'invalid-uuid',
            '12345',
            'not-a-uuid-at-all',
            '550e8400-e29b-41d4-a716-44665544000',  // too short
            '550e8400-e29b-41d4-a716-4466554400000', // too long
            '550e8400-e29b-41d4-a716-446655440000-extra', // extra chars
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ', // invalid chars
        ];

        foreach ($invalidUuids as $invalidUuid) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('Invalid UUID format for User ID');
            
            UserId::fromString($invalidUuid);
        }
    }

    /**
     * @test
     */
    public function it_should_implement_equals_correctly(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $userId1 = UserId::fromString($uuid);
        $userId2 = UserId::fromString($uuid);
        $userId3 = UserId::fromString('550e8400-e29b-41d4-a716-446655440001');
        
        $this->assertValueObjectsEqual($userId1, $userId2);
        $this->assertValueObjectsNotEqual($userId1, $userId3);
    }

    /**
     * @test
     */
    public function it_should_implement_equals_with_different_case(): void
    {
        $lowerUuid = '550e8400-e29b-41d4-a716-446655440000';
        $upperUuid = '550E8400-E29B-41D4-A716-446655440000';
        
        $userId1 = UserId::fromString($lowerUuid);
        $userId2 = UserId::fromString($upperUuid);
        
        $this->assertValueObjectsEqual($userId1, $userId2);
    }

    /**
     * @test
     */
    public function it_should_implement_toString_correctly(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $userId = UserId::fromString($uuid);
        
        $this->assertEquals($uuid, $userId->toString());
        $this->assertEquals($uuid, (string) $userId);
    }

    /**
     * @test
     */
    public function it_should_prevent_cloning_for_immutability(): void
    {
        $userId = UserId::generate();
        
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('UserId is immutable and cannot be cloned');
        
        clone $userId;
    }

    /**
     * @test
     */
    public function it_should_prevent_unserialization_for_immutability(): void
    {
        $userId = UserId::generate();
        
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('UserId cannot be unserialized');
        
        $userId->__wakeup();
    }

    /**
     * @test
     */
    public function it_should_generate_unique_ids(): void
    {
        $userId1 = UserId::generate();
        $userId2 = UserId::generate();
        
        $this->assertNotEquals($userId1->toString(), $userId2->toString());
        $this->assertValueObjectsNotEqual($userId1, $userId2);
    }

    /**
     * @test
     */
    public function it_should_handle_null_input_gracefully(): void
    {
        $this->expectException(\TypeError::class);
        
        // @phpstan-ignore-next-line - intentionally passing null
        UserId::fromString(null);
    }

    /**
     * @test
     */
    public function it_should_preserve_uuid_format(): void
    {
        $mixedCaseUuid = '550e8400-E29B-41d4-A716-446655440000';
        $userId = UserId::fromString($mixedCaseUuid);
        
        // Should preserve the original format or normalize consistently
        $result = $userId->toString();
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $result
        );
    }

    /**
     * @test
     */
    public function it_should_implement_value_object_contract(): void
    {
        $userId = UserId::generate();
        
        // Has toString method
        $this->assertTrue(method_exists($userId, 'toString'));
        
        // Has equals method
        $this->assertTrue(method_exists($userId, 'equals'));
        
        // Has __toString method
        $this->assertTrue(method_exists($userId, '__toString'));
        
        // toString returns string
        $this->assertIsString($userId->toString());
        
        // equals returns bool
        $this->assertIsBool($userId->equals($userId));
    }
}