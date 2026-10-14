<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Phone;

use PetrosLabs\FrValidator\ValidationResult;

/**
 * Validates a French phone number: 10 digits starting with 0, where the
 * second digit (1-9) determines the type (01-05 geographic, 06-07 mobile,
 * 08 special services, 09 non-geographic/VoIP) — or the international form
 * ("+33"/"0033" + the same number with the leading 0 dropped).
 *
 * Scope (v1): format only, not type classification (mobile vs landline vs
 * VoIP) and not DOM-specific rules — overseas numbers already follow the
 * same 10-digit "0Z..." structure since the early 2000s, so no separate
 * handling is needed. The "+33(0)..." notation some sites use is **not**
 * supported; strip the parenthesized 0 yourself before validating.
 *
 * @see https://fr.wikipedia.org/wiki/Plan_de_num%C3%A9rotation_en_France
 */
final class PhoneValidator implements PhoneValidatorInterface
{
    private const NATIONAL_LENGTH = 10;

    private const INTERNATIONAL_SUFFIX_LENGTH = 9;

    public function validate(string $phone): ValidationResult
    {
        $normalized = str_replace([' ', '.', '-'], '', $phone);

        if (str_starts_with($normalized, '+33')) {
            return $this->validateInternational(substr($normalized, 3));
        }

        if (str_starts_with($normalized, '0033')) {
            return $this->validateInternational(substr($normalized, 4));
        }

        return $this->validateNational($normalized);
    }

    private function validateNational(string $normalized): ValidationResult
    {
        if (strlen($normalized) !== self::NATIONAL_LENGTH) {
            return ValidationResult::failure(PhoneFailureReason::INVALID_LENGTH);
        }

        /**
         * $normalized is already exactly NATIONAL_LENGTH (10) characters,
         * so the ^ and $ anchors can never change the match result.
         *
         * @infection-ignore-all
         */
        if (preg_match('/^0[1-9]\d{8}$/', $normalized) !== 1) {
            return ValidationResult::failure(PhoneFailureReason::INVALID_FORMAT);
        }

        return ValidationResult::success();
    }

    private function validateInternational(string $suffix): ValidationResult
    {
        if (strlen($suffix) !== self::INTERNATIONAL_SUFFIX_LENGTH) {
            return ValidationResult::failure(PhoneFailureReason::INVALID_LENGTH);
        }

        /**
         * $suffix is already exactly INTERNATIONAL_SUFFIX_LENGTH (9)
         * characters, so the ^ and $ anchors can never change the match
         * result.
         *
         * @infection-ignore-all
         */
        if (preg_match('/^[1-9]\d{8}$/', $suffix) !== 1) {
            return ValidationResult::failure(PhoneFailureReason::INVALID_FORMAT);
        }

        return ValidationResult::success();
    }
}
