<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Bic;

use PetrosLabs\FrValidator\ValidationResult;

/**
 * Validates a BIC/SWIFT code (ISO 9362): 4-letter bank code + 2-letter
 * country code + 2-character location code + an optional 3-character
 * branch code (8 characters without it, 11 with it; "XXX" conventionally
 * means the primary office).
 *
 * Unlike this package's other validators, this is a general, international
 * format check, not France-specific — the standard has no French variant,
 * and restricting it to country code "FR" would make it useless for
 * validating the BIC side of a SEPA transfer to a non-French bank. There is
 * no checksum digit: a syntactically valid BIC isn't guaranteed to be a
 * real, registered one.
 *
 * @see https://en.wikipedia.org/wiki/ISO_9362
 */
final class BicValidator implements BicValidatorInterface
{
    private const SHORT_LENGTH = 8;

    private const LONG_LENGTH = 11;

    public function validate(string $bic): ValidationResult
    {
        $normalized = strtoupper(str_replace(' ', '', $bic));

        $length = strlen($normalized);

        if ($length !== self::SHORT_LENGTH && $length !== self::LONG_LENGTH) {
            return ValidationResult::failure(BicFailureReason::INVALID_LENGTH);
        }

        $bankCode = substr($normalized, 0, 4);

        /**
         * $normalized is already exactly SHORT_LENGTH or LONG_LENGTH
         * characters (both >= 4), so the ^ and $ anchors can never change
         * the match result for this fixed-offset slice.
         *
         * @infection-ignore-all
         */
        if (preg_match('/^[A-Z]{4}$/', $bankCode) !== 1) {
            return ValidationResult::failure(BicFailureReason::INVALID_BANK_CODE);
        }

        $countryCode = substr($normalized, 4, 2);

        /** @infection-ignore-all */
        if (preg_match('/^[A-Z]{2}$/', $countryCode) !== 1) {
            return ValidationResult::failure(BicFailureReason::INVALID_COUNTRY_CODE);
        }

        $locationCode = substr($normalized, 6, 2);

        /** @infection-ignore-all */
        if (preg_match('/^[A-Z0-9]{2}$/', $locationCode) !== 1) {
            return ValidationResult::failure(BicFailureReason::INVALID_LOCATION_CODE);
        }

        if ($length === self::LONG_LENGTH) {
            $branchCode = substr($normalized, 8, 3);

            /** @infection-ignore-all */
            if (preg_match('/^[A-Z0-9]{3}$/', $branchCode) !== 1) {
                return ValidationResult::failure(BicFailureReason::INVALID_BRANCH_CODE);
            }
        }

        return ValidationResult::success();
    }
}
