<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Tests\Siren;

use PetrosLabs\FrValidator\Siren\SirenFailureReason;
use PetrosLabs\FrValidator\Siren\SirenValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Test vectors are computed independently via the documented Luhn formula
 * (see SirenValidator docblock), not copied from an online "example".
 */
final class SirenValidatorTest extends TestCase
{
    private SirenValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new SirenValidator();
    }

    #[Test]
    #[DataProvider('validSirenProvider')]
    public function it_accepts_a_valid_siren(string $siren): void
    {
        self::assertTrue($this->validator->validate($siren)->isValid());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validSirenProvider(): iterable
    {
        yield 'standard' => ['732829320'];
        yield 'with spaces' => ['732 829 320'];
    }

    #[Test]
    #[DataProvider('invalidSirenProvider')]
    public function it_rejects_an_invalid_siren(string $siren, SirenFailureReason $expectedReason): void
    {
        $result = $this->validator->validate($siren);

        self::assertFalse($result->isValid());
        self::assertSame($expectedReason, $result->failureReason());
    }

    /**
     * @return iterable<string, array{string, SirenFailureReason}>
     */
    public static function invalidSirenProvider(): iterable
    {
        yield 'too short' => ['73282932', SirenFailureReason::INVALID_LENGTH];
        yield 'too long' => ['7328293200', SirenFailureReason::INVALID_LENGTH];
        yield 'non-digit character' => ['73282932A', SirenFailureReason::INVALID_FORMAT];
        yield 'wrong checksum' => ['732829321', SirenFailureReason::INVALID_CHECKSUM];
    }
}
