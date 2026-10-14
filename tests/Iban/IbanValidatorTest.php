<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Tests\Iban;

use PetrosLabs\FrValidator\Iban\IbanFailureReason;
use PetrosLabs\FrValidator\Iban\IbanValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Test vectors are computed independently via the documented ISO 7064
 * MOD 97-10 formula (see IbanValidator docblock), cross-checked against the
 * worked GB example on Wikipedia's IBAN article before being adapted to a
 * French IBAN built on top of the already-verified RIB test vector.
 */
final class IbanValidatorTest extends TestCase
{
    private IbanValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new IbanValidator();
    }

    #[Test]
    #[DataProvider('validIbanProvider')]
    public function it_accepts_a_valid_iban(string $iban): void
    {
        self::assertTrue($this->validator->validate($iban)->isValid());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validIbanProvider(): iterable
    {
        yield 'standard' => ['FR7630003018900003755348521'];
        yield 'with spaces, lowercase country code' => ['fr76 3000 3018 9000 0375 5348 521'];
        yield 'letter in account number (matches Wikipedia RIB example)' => ['FR8412345123451234567891A16'];
    }

    #[Test]
    #[DataProvider('invalidIbanProvider')]
    public function it_rejects_an_invalid_iban(string $iban, IbanFailureReason $expectedReason): void
    {
        $result = $this->validator->validate($iban);

        self::assertFalse($result->isValid());
        self::assertSame($expectedReason, $result->failureReason());
    }

    /**
     * @return iterable<string, array{string, IbanFailureReason}>
     */
    public static function invalidIbanProvider(): iterable
    {
        yield 'too short' => ['FR763000301890000375534852', IbanFailureReason::INVALID_LENGTH];
        yield 'too long' => ['FR76300030189000037553485211', IbanFailureReason::INVALID_LENGTH];
        yield 'non-French country code' => ['DE7630003018900003755348521', IbanFailureReason::INVALID_COUNTRY_CODE];
        yield 'non-digit check digits' => ['FRXX30003018900003755348521', IbanFailureReason::INVALID_FORMAT];
        yield 'non-digit check digit, first position' => ['FRX630003018900003755348521', IbanFailureReason::INVALID_FORMAT];
        yield 'embedded RIB has the wrong key' => ['FR0730003018900003755348599', IbanFailureReason::INVALID_RIB];
        yield 'wrong IBAN checksum, valid RIB' => ['FR0030003018900003755348521', IbanFailureReason::INVALID_CHECKSUM];
    }
}
