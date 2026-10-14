<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Tests\Naf;

use PetrosLabs\FrValidator\Naf\NafFailureReason;
use PetrosLabs\FrValidator\Naf\NafValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * No checksum exists for NAF codes (see NafValidator docblock) — validity
 * is membership in the official 732-code list, not a computation. "6201Z"
 * (Programmation informatique) is a real, well-known code used here as a
 * stable example, not a fabricated one.
 */
final class NafValidatorTest extends TestCase
{
    private NafValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new NafValidator();
    }

    #[Test]
    #[DataProvider('validNafProvider')]
    public function it_accepts_a_valid_naf_code(string $naf): void
    {
        self::assertTrue($this->validator->validate($naf)->isValid());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validNafProvider(): iterable
    {
        yield 'without dot' => ['6201Z'];
        yield 'with dot' => ['62.01Z'];
        yield 'lowercase letter' => ['62.01z'];
    }

    #[Test]
    #[DataProvider('invalidNafProvider')]
    public function it_rejects_an_invalid_naf_code(string $naf, NafFailureReason $expectedReason): void
    {
        $result = $this->validator->validate($naf);

        self::assertFalse($result->isValid());
        self::assertSame($expectedReason, $result->failureReason());
    }

    /**
     * @return iterable<string, array{string, NafFailureReason}>
     */
    public static function invalidNafProvider(): iterable
    {
        yield 'too short' => ['201Z', NafFailureReason::INVALID_LENGTH];
        yield 'too long' => ['62001Z', NafFailureReason::INVALID_LENGTH];
        yield 'letter in digit position' => ['620AZ', NafFailureReason::INVALID_FORMAT];
        yield 'digit in letter position' => ['62019', NafFailureReason::INVALID_FORMAT];
        yield 'well-formed but not in the nomenclature' => ['0000A', NafFailureReason::UNKNOWN_CODE];
    }

    #[Test]
    public function it_accepts_a_code_not_in_the_nomenclature_when_passed_as_additional(): void
    {
        $validator = new NafValidator(['0000A']);

        self::assertTrue($validator->validate('0000A')->isValid());
    }

    #[Test]
    public function it_still_rejects_unlisted_codes_when_additional_codes_are_set(): void
    {
        $validator = new NafValidator(['0000A']);

        $result = $validator->validate('9999Z');

        self::assertFalse($result->isValid());
        self::assertSame(NafFailureReason::UNKNOWN_CODE, $result->failureReason());
    }
}
