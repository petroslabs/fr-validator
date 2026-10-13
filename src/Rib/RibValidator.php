<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Rib;

use PetrosLabs\FrValidator\ValidationResult;

/**
 * Validates the French RIB key (clé RIB): bank code (5 digits) + branch code
 * (5 digits) + account number (11 alphanumeric characters) + check key
 * (2 digits), 23 characters total.
 *
 * @see https://fr.wikipedia.org/wiki/Cl%C3%A9_RIB
 */
final class RibValidator implements RibValidatorInterface
{
    private const LENGTH = 23;

    private const BANK_CODE_LENGTH = 5;

    private const BRANCH_CODE_LENGTH = 5;

    private const ACCOUNT_NUMBER_LENGTH = 11;

    /** Official letter-to-digit conversion table used in the checksum. */
    private const LETTER_TO_DIGIT = [
        'A' => '1', 'J' => '1',
        'B' => '2', 'K' => '2', 'S' => '2',
        'C' => '3', 'L' => '3', 'T' => '3',
        'D' => '4', 'M' => '4', 'U' => '4',
        'E' => '5', 'N' => '5', 'V' => '5',
        'F' => '6', 'O' => '6', 'W' => '6',
        'G' => '7', 'P' => '7', 'X' => '7',
        'H' => '8', 'Q' => '8', 'Y' => '8',
        'I' => '9', 'R' => '9', 'Z' => '9',
    ];

    public function validate(string $rib): ValidationResult
    {
        $normalized = strtoupper(str_replace([' ', '-'], '', $rib));

        if (strlen($normalized) !== self::LENGTH) {
            return ValidationResult::failure(RibFailureReason::INVALID_LENGTH);
        }

        $bankCode = substr($normalized, 0, self::BANK_CODE_LENGTH);
        $branchCode = substr($normalized, self::BANK_CODE_LENGTH, self::BRANCH_CODE_LENGTH);
        $accountNumber = substr($normalized, self::BANK_CODE_LENGTH + self::BRANCH_CODE_LENGTH, self::ACCOUNT_NUMBER_LENGTH);
        $key = substr($normalized, self::BANK_CODE_LENGTH + self::BRANCH_CODE_LENGTH + self::ACCOUNT_NUMBER_LENGTH, 2);

        if (preg_match('/^\d{5}$/', $bankCode) !== 1) {
            return ValidationResult::failure(RibFailureReason::INVALID_BANK_CODE);
        }

        if (preg_match('/^\d{5}$/', $branchCode) !== 1) {
            return ValidationResult::failure(RibFailureReason::INVALID_BRANCH_CODE);
        }

        if (preg_match('/^[A-Z0-9]{11}$/', $accountNumber) !== 1) {
            return ValidationResult::failure(RibFailureReason::INVALID_ACCOUNT_NUMBER);
        }

        if (preg_match('/^\d{2}$/', $key) !== 1) {
            return ValidationResult::failure(RibFailureReason::INVALID_FORMAT);
        }

        $convertedAccountNumber = strtr($accountNumber, self::LETTER_TO_DIGIT);

        $checksum = 89 * (int) $bankCode + 15 * (int) $branchCode + 3 * (int) $convertedAccountNumber;
        $expectedKey = 97 - ($checksum % 97);

        if ((int) $key !== $expectedKey) {
            return ValidationResult::failure(RibFailureReason::INVALID_CHECKSUM);
        }

        return ValidationResult::success();
    }
}
