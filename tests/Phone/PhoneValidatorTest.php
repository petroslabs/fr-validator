<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Tests\Phone;

use PetrosLabs\FrValidator\Phone\PhoneFailureReason;
use PetrosLabs\FrValidator\Phone\PhoneValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * No checksum exists for French phone numbers, so there's nothing to
 * compute against an oracle — these vectors exercise the documented
 * structural rules (see PhoneValidator docblock) directly.
 */
final class PhoneValidatorTest extends TestCase
{
    private PhoneValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new PhoneValidator();
    }

    #[Test]
    #[DataProvider('validPhoneProvider')]
    public function it_accepts_a_valid_phone_number(string $phone): void
    {
        self::assertTrue($this->validator->validate($phone)->isValid());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validPhoneProvider(): iterable
    {
        yield 'mobile' => ['0612345678'];
        yield 'landline with spaces' => ['01 23 45 67 89'];
        yield 'landline with dots and dashes' => ['01.23-45.67-89'];
        yield 'international with +33' => ['+33612345678'];
        yield 'international with +33 and spaces' => ['+33 6 12 34 56 78'];
        yield 'international with 0033' => ['0033612345678'];
    }

    #[Test]
    #[DataProvider('invalidPhoneProvider')]
    public function it_rejects_an_invalid_phone_number(string $phone, PhoneFailureReason $expectedReason): void
    {
        $result = $this->validator->validate($phone);

        self::assertFalse($result->isValid());
        self::assertSame($expectedReason, $result->failureReason());
    }

    /**
     * @return iterable<string, array{string, PhoneFailureReason}>
     */
    public static function invalidPhoneProvider(): iterable
    {
        yield 'too short' => ['061234567', PhoneFailureReason::INVALID_LENGTH];
        yield 'too long' => ['06123456789', PhoneFailureReason::INVALID_LENGTH];
        yield 'does not start with 0' => ['1612345678', PhoneFailureReason::INVALID_FORMAT];
        yield 'second digit is 0' => ['0012345678', PhoneFailureReason::INVALID_FORMAT];
        yield 'letters in the number' => ['06123456AB', PhoneFailureReason::INVALID_FORMAT];
        yield 'international suffix too short' => ['+3361234567', PhoneFailureReason::INVALID_LENGTH];
        yield 'international suffix too long' => ['+3361234567890', PhoneFailureReason::INVALID_LENGTH];
        yield 'international suffix starts with 0' => ['+33012345678', PhoneFailureReason::INVALID_FORMAT];
        yield 'international (0033 prefix) suffix starts with 0' => ['0033012345678', PhoneFailureReason::INVALID_FORMAT];
    }
}
