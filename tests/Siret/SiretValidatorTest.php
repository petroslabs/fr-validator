<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Tests\Siret;

use PetrosLabs\FrValidator\Siret\SiretFailureReason;
use PetrosLabs\FrValidator\Siret\SiretValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Test vectors are computed independently via the documented double-Luhn
 * formula (see SiretValidator docblock). "73282932000074" also appears as
 * a worked example on the French Wikipedia SIRET article, used as an
 * external cross-check, not as the sole source of truth.
 */
final class SiretValidatorTest extends TestCase
{
    private SiretValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new SiretValidator();
    }

    #[Test]
    #[DataProvider('validSiretProvider')]
    public function it_accepts_a_valid_siret(string $siret): void
    {
        self::assertTrue($this->validator->validate($siret)->isValid());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validSiretProvider(): iterable
    {
        yield 'standard (matches Wikipedia worked example)' => ['73282932000074'];
        yield 'with spaces' => ['732 829 320 00074'];
        yield 'la poste headquarters (satisfies standard Luhn directly)' => ['35600000000048'];
        yield 'la poste other establishment (Luhn fails, sum-of-5 fallback saves it)' => ['35600000000001'];
    }

    #[Test]
    #[DataProvider('invalidSiretProvider')]
    public function it_rejects_an_invalid_siret(string $siret, SiretFailureReason $expectedReason): void
    {
        $result = $this->validator->validate($siret);

        self::assertFalse($result->isValid());
        self::assertSame($expectedReason, $result->failureReason());
    }

    /**
     * @return iterable<string, array{string, SiretFailureReason}>
     */
    public static function invalidSiretProvider(): iterable
    {
        yield 'too short' => ['7328293200007', SiretFailureReason::INVALID_LENGTH];
        yield 'too long' => ['732829320000740', SiretFailureReason::INVALID_LENGTH];
        yield 'non-digit character' => ['7328293200007A', SiretFailureReason::INVALID_FORMAT];
        yield 'invalid embedded siren' => ['73282932100074', SiretFailureReason::INVALID_SIREN_CHECKSUM];
        yield 'valid siren, wrong siret checksum' => ['73282932000070', SiretFailureReason::INVALID_CHECKSUM];
        yield 'la poste, neither Luhn nor sum-of-5 holds' => ['35600000000002', SiretFailureReason::INVALID_CHECKSUM];
    }
}
