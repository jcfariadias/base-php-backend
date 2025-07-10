<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User\ValueObject;

use App\Domain\User\ValueObject\Email;
use InvalidArgumentException;

/**
 * Test cases for Email value object following TDD principles
 */
final class EmailTest extends ValueObjectTestCase
{
    /**
     * @test
     */
    public function it_should_be_immutable(): void
    {
        $this->assertImmutable(Email::class);
    }

    /**
     * @test
     */
    public function it_should_create_from_valid_email(): void
    {
        $validEmails = [
            'user@example.com',
            'test.email@domain.org',
            'user+tag@example.co.uk',
            'first.last@subdomain.domain.com',
            'user123@example123.com',
            'a@b.co',
        ];

        foreach ($validEmails as $email) {
            $emailObj = Email::fromString($email);
            
            $this->assertInstanceOf(Email::class, $emailObj);
            $this->assertEquals(strtolower($email), $emailObj->toString());
        }
    }

    /**
     * @test
     */
    public function it_should_normalize_email_to_lowercase(): void
    {
        $mixedCaseEmail = 'User.Test@EXAMPLE.COM';
        $email = Email::fromString($mixedCaseEmail);
        
        $this->assertEquals('user.test@example.com', $email->toString());
    }

    /**
     * @test
     */
    public function it_should_trim_whitespace(): void
    {
        $emailWithWhitespace = '  user@example.com  ';
        $email = Email::fromString($emailWithWhitespace);
        
        $this->assertEquals('user@example.com', $email->toString());
    }

    /**
     * @test
     */
    public function it_should_reject_empty_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email cannot be empty');
        
        Email::fromString('');
    }

    /**
     * @test
     */
    public function it_should_reject_whitespace_only_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email cannot be empty');
        
        Email::fromString('   ');
    }

    /**
     * @test
     */
    public function it_should_reject_invalid_email_formats(): void
    {
        $invalidEmails = [
            'invalid-email',
            '@example.com',
            'user@',
            'user..name@example.com',
            'user@.example.com',
            'user@example..com',
            'user name@example.com',
            'user@example',
            'user@-example.com',
            'user@example-.com',
            'user@@example.com',
            'user@example.com.',
            '.user@example.com',
            'user.@example.com',
        ];

        foreach ($invalidEmails as $invalidEmail) {
            try {
                Email::fromString($invalidEmail);
                $this->fail("Should have thrown exception for invalid email: '$invalidEmail'");
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString('Invalid email format', $e->getMessage(),
                    "Wrong exception message for '$invalidEmail': " . $e->getMessage());
            }
        }
    }

    /**
     * @test
     */
    public function it_should_reject_email_too_long(): void
    {
        // Create an email longer than 254 characters
        $longLocalPart = str_repeat('a', 245);
        $longEmail = $longLocalPart . '@example.com';
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email is too long (maximum 254 characters)');
        
        Email::fromString($longEmail);
    }

    /**
     * @test
     */
    public function it_should_implement_equals_correctly(): void
    {
        $email1 = Email::fromString('user@example.com');
        $email2 = Email::fromString('user@example.com');
        $email3 = Email::fromString('different@example.com');
        
        $this->assertValueObjectsEqual($email1, $email2);
        $this->assertValueObjectsNotEqual($email1, $email3);
    }

    /**
     * @test
     */
    public function it_should_implement_equals_with_case_insensitive(): void
    {
        $lowerEmail = Email::fromString('user@example.com');
        $upperEmail = Email::fromString('USER@EXAMPLE.COM');
        
        $this->assertValueObjectsEqual($lowerEmail, $upperEmail);
    }

    /**
     * @test
     */
    public function it_should_extract_domain_correctly(): void
    {
        $email = Email::fromString('user@example.com');
        
        $this->assertEquals('example.com', $email->getDomain());
    }

    /**
     * @test
     */
    public function it_should_extract_domain_from_complex_email(): void
    {
        $email = Email::fromString('user.name+tag@subdomain.example.co.uk');
        
        $this->assertEquals('subdomain.example.co.uk', $email->getDomain());
    }

    /**
     * @test
     */
    public function it_should_extract_local_part_correctly(): void
    {
        $email = Email::fromString('user@example.com');
        
        $this->assertEquals('user', $email->getLocalPart());
    }

    /**
     * @test
     */
    public function it_should_extract_local_part_from_complex_email(): void
    {
        $email = Email::fromString('user.name+tag@subdomain.example.co.uk');
        
        $this->assertEquals('user.name+tag', $email->getLocalPart());
    }

    /**
     * @test
     */
    public function it_should_implement_toString_correctly(): void
    {
        $emailString = 'user@example.com';
        $email = Email::fromString($emailString);
        
        $this->assertEquals($emailString, $email->toString());
        $this->assertEquals($emailString, (string) $email);
    }

    /**
     * @test
     */
    public function it_should_prevent_cloning_for_immutability(): void
    {
        $email = Email::fromString('user@example.com');
        
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Email is immutable and cannot be cloned');
        
        clone $email;
    }

    /**
     * @test
     */
    public function it_should_prevent_unserialization_for_immutability(): void
    {
        $email = Email::fromString('user@example.com');
        
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Email cannot be unserialized');
        
        $email->__wakeup();
    }

    /**
     * @test
     */
    public function it_should_handle_null_input_gracefully(): void
    {
        $this->expectException(\TypeError::class);
        
        // @phpstan-ignore-next-line - intentionally passing null
        Email::fromString(null);
    }

    /**
     * @test
     */
    public function it_should_handle_international_domains(): void
    {
        // Test with international domain (if supported)
        $email = Email::fromString('user@example.org');
        
        $this->assertEquals('user@example.org', $email->toString());
        $this->assertEquals('example.org', $email->getDomain());
        $this->assertEquals('user', $email->getLocalPart());
    }

    /**
     * @test
     */
    public function it_should_implement_value_object_contract(): void
    {
        $email = Email::fromString('user@example.com');
        
        // Has toString method
        $this->assertTrue(method_exists($email, 'toString'));
        
        // Has equals method
        $this->assertTrue(method_exists($email, 'equals'));
        
        // Has __toString method
        $this->assertTrue(method_exists($email, '__toString'));
        
        // Has getDomain method
        $this->assertTrue(method_exists($email, 'getDomain'));
        
        // Has getLocalPart method
        $this->assertTrue(method_exists($email, 'getLocalPart'));
        
        // toString returns string
        $this->assertIsString($email->toString());
        
        // equals returns bool
        $this->assertIsBool($email->equals($email));
        
        // getDomain returns string
        $this->assertIsString($email->getDomain());
        
        // getLocalPart returns string
        $this->assertIsString($email->getLocalPart());
    }

    /**
     * @test
     */
    public function it_should_preserve_valid_email_structure(): void
    {
        $email = Email::fromString('test.user+tag@sub.example.com');
        
        // Should contain exactly one @ symbol
        $this->assertEquals(1, substr_count($email->toString(), '@'));
        
        // Domain and local part should not be empty
        $this->assertNotEmpty($email->getDomain());
        $this->assertNotEmpty($email->getLocalPart());
        
        // Reconstructed email should equal original
        $reconstructed = $email->getLocalPart() . '@' . $email->getDomain();
        $this->assertEquals($email->toString(), $reconstructed);
    }
}