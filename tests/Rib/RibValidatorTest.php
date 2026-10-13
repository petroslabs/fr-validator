<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Tests\Rib;

use PetrosLabs\FrValidator\Rib\RibFailureReason;
use PetrosLabs\FrValidator\Rib\RibValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Test vectors are computed independently via the documented
 * `key = 97 - ((89*bank + 15*branch + 3*account) mod 97)` formula (see
 * RibValidator docblock), with letters converted per the official table.
 * The "with letters" vector matches the worked example on the French
 * Wikipedia "Clé RIB" article (key 16), used as an external cross-check.
 */
final class RibValidatorTest extends TestCase
{
    private RibValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new RibValidator();
    }

    #[Test]
    #[DataProvider('validRibProvider')]
    public function it_accepts_a_valid_rib(string $rib): void
    {
        self::assertTrue($this->validator->validate($rib)->isValid());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validRibProvider(): iterable
    {
        yield 'all digits' => ['30003018900003755348521'];
        yield 'with letters (matches Wikipedia worked example)' => ['12345123451234567891A16'];
        yield 'with letters, lowercase and separators' => ['20041 01005 0500013m026 06'];
    }

    #[Test]
    #[DataProvider('invalidRibProvider')]
    public function it_rejects_an_invalid_rib(string $rib, RibFailureReason $expectedReason): void
    {
        $result = $this->validator->validate($rib);

        self::assertFalse($result->isValid());
        self::assertSame($expectedReason, $result->failureReason());
    }

    /**
     * @return iterable<string, array{string, RibFailureReason}>
     */
    public static function invalidRibProvider(): iterable
    {
        yield 'too short' => ['3000301890000375534852', RibFailureReason::INVALID_LENGTH];
        yield 'too long' => ['30003018900003755348521X', RibFailureReason::INVALID_LENGTH];
        yield 'non-digit bank code' => ['3000A018900003755348521', RibFailureReason::INVALID_BANK_CODE];
        yield 'non-digit branch code' => ['300030189A0003755348521', RibFailureReason::INVALID_BRANCH_CODE];
        yield 'disallowed character in account number' => ['30003018900003755348!21', RibFailureReason::INVALID_ACCOUNT_NUMBER];
        yield 'non-digit key' => ['3000301890000375534852X', RibFailureReason::INVALID_FORMAT];
        yield 'wrong checksum' => ['30003018900003755348599', RibFailureReason::INVALID_CHECKSUM];
    }
}
