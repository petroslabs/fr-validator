<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Tests\Bic;

use PetrosLabs\FrValidator\Bic\BicFailureReason;
use PetrosLabs\FrValidator\Bic\BicValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * BIC has no checksum (see BicValidator docblock), so there's nothing to
 * compute or cross-check against an external oracle — these are plain
 * synthetic examples exercising the character-class/length rules, not real
 * bank identifiers.
 */
final class BicValidatorTest extends TestCase
{
    private BicValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new BicValidator();
    }

    #[Test]
    #[DataProvider('validBicProvider')]
    public function it_accepts_a_valid_bic(string $bic): void
    {
        self::assertTrue($this->validator->validate($bic)->isValid());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validBicProvider(): iterable
    {
        yield '8 characters, primary office implied' => ['ABCDFRPP'];
        yield '11 characters with explicit branch code' => ['ABCDFRPPXXX'];
        yield 'digits allowed in location and branch code' => ['ABCDFR2L1X9'];
        yield 'with spaces and lowercase' => ['abcd fr pp'];
    }

    #[Test]
    #[DataProvider('invalidBicProvider')]
    public function it_rejects_an_invalid_bic(string $bic, BicFailureReason $expectedReason): void
    {
        $result = $this->validator->validate($bic);

        self::assertFalse($result->isValid());
        self::assertSame($expectedReason, $result->failureReason());
    }

    /**
     * @return iterable<string, array{string, BicFailureReason}>
     */
    public static function invalidBicProvider(): iterable
    {
        yield 'too short' => ['ABCDFRP', BicFailureReason::INVALID_LENGTH];
        yield 'in between 8 and 11' => ['ABCDFRPPX', BicFailureReason::INVALID_LENGTH];
        yield 'too long' => ['ABCDFRPPXXXX', BicFailureReason::INVALID_LENGTH];
        yield 'digit in bank code' => ['ABC1FRPP', BicFailureReason::INVALID_BANK_CODE];
        yield 'digit in bank code, first position' => ['1BCDFRPP', BicFailureReason::INVALID_BANK_CODE];
        yield 'digit in country code' => ['ABCDF1PP', BicFailureReason::INVALID_COUNTRY_CODE];
        yield 'disallowed character in location code' => ['ABCDFR!P', BicFailureReason::INVALID_LOCATION_CODE];
        yield 'disallowed character in branch code' => ['ABCDFRPPX!X', BicFailureReason::INVALID_BRANCH_CODE];
    }
}
