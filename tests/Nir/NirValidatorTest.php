<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Tests\Nir;

use PetrosLabs\FrValidator\Nir\NirFailureReason;
use PetrosLabs\FrValidator\Nir\NirValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Test vectors below are computed independently via the documented
 * `key = 97 - (base13 mod 97)` formula (see NirValidator docblock), not copied
 * from third-party "example NIR" pages — several of those circulate with a
 * checksum that doesn't actually satisfy the formula.
 */
final class NirValidatorTest extends TestCase
{
    private NirValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new NirValidator();
    }

    #[Test]
    #[DataProvider('validNirProvider')]
    public function it_accepts_a_valid_nir(string $nir): void
    {
        self::assertTrue($this->validator->validate($nir)->isValid());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validNirProvider(): iterable
    {
        yield 'mainland department' => ['185037511601216'];
        yield 'corsica 2A' => ['290052A03301071'];
        yield 'corsica 2B' => ['177112B00100701'];
        yield 'overseas department 97' => ['295079712304561'];
        yield 'lowercase corsica letter is normalized' => ['290052a03301071'];
        yield 'spaces are stripped' => ['1 85 03 75 116 012 16'];
    }

    #[Test]
    #[DataProvider('invalidNirProvider')]
    public function it_rejects_an_invalid_nir(string $nir, NirFailureReason $expectedReason): void
    {
        $result = $this->validator->validate($nir);

        self::assertFalse($result->isValid());
        self::assertSame($expectedReason, $result->failureReason());
    }

    /**
     * @return iterable<string, array{string, NirFailureReason}>
     */
    public static function invalidNirProvider(): iterable
    {
        yield 'too short' => ['18503751160121', NirFailureReason::INVALID_LENGTH];
        yield 'too long' => ['1850375116012166', NirFailureReason::INVALID_LENGTH];
        yield 'invalid sex digit' => ['585037511601216', NirFailureReason::INVALID_SEX_DIGIT];
        yield 'invalid month 13' => ['185137511601216', NirFailureReason::INVALID_MONTH];
        yield 'invalid month 00' => ['185007511601216', NirFailureReason::INVALID_MONTH];
        yield 'department 00 does not exist' => ['185030011601216', NirFailureReason::INVALID_DEPARTMENT_CODE];
        yield 'department 20 does not exist (split into 2A/2B)' => ['185032011601216', NirFailureReason::INVALID_DEPARTMENT_CODE];
        yield 'department 96 does not exist' => ['185039611601216', NirFailureReason::INVALID_DEPARTMENT_CODE];
        yield 'non-digit commune code' => ['185037511X01216', NirFailureReason::INVALID_FORMAT];
        yield 'non-digit order number' => ['18503751160X216', NirFailureReason::INVALID_FORMAT];
        yield 'wrong checksum' => ['185037511601299', NirFailureReason::INVALID_CHECKSUM];
    }
}
