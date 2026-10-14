<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Tests\Vat;

use PetrosLabs\FrValidator\Vat\VatFailureReason;
use PetrosLabs\FrValidator\Vat\VatValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Test vectors are computed independently via the documented
 * `key = (12 + 3 * (SIREN mod 97)) mod 97` formula (see VatValidator
 * docblock) — a widely circulated "worked example" online computes
 * 123456789 mod 97 as 36 and the key as 23; both are wrong (it's 39 and
 * 32), confirmed by two independent manual recomputations. Two valid
 * vectors with different SIRENs are used on purpose: a single one could
 * coincidentally satisfy a broken implementation (this bit the IBAN
 * validator once already).
 */
final class VatValidatorTest extends TestCase
{
    private VatValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new VatValidator();
    }

    #[Test]
    #[DataProvider('validVatProvider')]
    public function it_accepts_a_valid_vat_number(string $vat): void
    {
        self::assertTrue($this->validator->validate($vat)->isValid());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validVatProvider(): iterable
    {
        yield 'standard' => ['FR44732829320'];
        yield 'different siren, different key' => ['FR83404833048'];
        yield 'with spaces, lowercase country code' => ['fr 44 732 829 320'];
    }

    #[Test]
    #[DataProvider('invalidVatProvider')]
    public function it_rejects_an_invalid_vat_number(string $vat, VatFailureReason $expectedReason): void
    {
        $result = $this->validator->validate($vat);

        self::assertFalse($result->isValid());
        self::assertSame($expectedReason, $result->failureReason());
    }

    /**
     * @return iterable<string, array{string, VatFailureReason}>
     */
    public static function invalidVatProvider(): iterable
    {
        yield 'too short' => ['FR4473282932', VatFailureReason::INVALID_LENGTH];
        yield 'too long' => ['FR447328293200', VatFailureReason::INVALID_LENGTH];
        yield 'non-French country code' => ['DE44732829320', VatFailureReason::INVALID_COUNTRY_CODE];
        yield 'non-digit key' => ['FRXX732829320', VatFailureReason::INVALID_FORMAT];
        yield 'non-digit key, first position' => ['FRX4732829320', VatFailureReason::INVALID_FORMAT];
        yield 'invalid embedded siren' => ['FR44732829321', VatFailureReason::INVALID_SIREN_CHECKSUM];
        yield 'valid siren, wrong key' => ['FR00732829320', VatFailureReason::INVALID_CHECKSUM];
    }
}
