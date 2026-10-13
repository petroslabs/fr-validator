<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Nir;

use PetrosLabs\FrValidator\ValidationResult;

/**
 * Validates the French social security number (NIR / numéro de sécurité sociale).
 *
 * Scope (v1): standard numbers only (sex digit 1 or 2, month 01-12).
 * Out of scope: temporary/technical registration numbers (sex digit 3, 4, 7, 8)
 * and the special month codes used for incomplete birth records — their exact
 * format could not be confirmed against a reliable source.
 *
 * @see https://fr.wikipedia.org/wiki/Num%C3%A9ro_de_s%C3%A9curit%C3%A9_sociale_en_France
 */
final class NirValidator implements NirValidatorInterface
{
    private const LENGTH = 15;

    private const DEPARTMENT_PATTERN = '/^(0[1-9]|1[0-9]|2[1-9]|[3-8][0-9]|9[0-5]|2[AB]|97|98|99)$/';

    private const MONTH_PATTERN = '/^(0[1-9]|1[0-2])$/';

    public function validate(string $nir): ValidationResult
    {
        $normalized = strtoupper(str_replace(' ', '', $nir));

        if (strlen($normalized) !== self::LENGTH) {
            return ValidationResult::failure(NirFailureReason::INVALID_LENGTH);
        }

        $sex = substr($normalized, 0, 1);
        if (!in_array($sex, ['1', '2'], true)) {
            return ValidationResult::failure(NirFailureReason::INVALID_SEX_DIGIT);
        }

        $month = substr($normalized, 3, 2);
        if (preg_match(self::MONTH_PATTERN, $month) !== 1) {
            return ValidationResult::failure(NirFailureReason::INVALID_MONTH);
        }

        $department = substr($normalized, 5, 2);
        if (preg_match(self::DEPARTMENT_PATTERN, $department) !== 1) {
            return ValidationResult::failure(NirFailureReason::INVALID_DEPARTMENT_CODE);
        }

        $year = substr($normalized, 1, 2);
        $commune = substr($normalized, 7, 3);
        $order = substr($normalized, 10, 3);
        $key = substr($normalized, 13, 2);

        if (preg_match('/^\d{2}$/', $year) !== 1
            || preg_match('/^\d{3}$/', $commune) !== 1
            || preg_match('/^\d{3}$/', $order) !== 1
            || preg_match('/^\d{2}$/', $key) !== 1
        ) {
            return ValidationResult::failure(NirFailureReason::INVALID_FORMAT);
        }

        // Corsica is spelled out with letters but computed as 19 (2A) / 18 (2B).
        $departmentForChecksum = strtr($department, ['2A' => '19', '2B' => '18']);
        $base = $sex . $year . $month . $departmentForChecksum . $commune . $order;

        $expectedKey = 97 - ((int) $base % 97);

        if ((int) $key !== $expectedKey) {
            return ValidationResult::failure(NirFailureReason::INVALID_CHECKSUM);
        }

        return ValidationResult::success();
    }
}
