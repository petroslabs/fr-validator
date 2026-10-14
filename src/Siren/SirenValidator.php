<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Siren;

use PetrosLabs\FrValidator\Internal\Luhn;
use PetrosLabs\FrValidator\ValidationResult;

/**
 * Validates a French company identifier (SIREN): 9 digits, valid when their
 * Luhn checksum is a multiple of 10.
 *
 * @see https://inseefrlab.github.io/validinsee/reference/validation_sirene.html
 */
final class SirenValidator implements SirenValidatorInterface
{
    private const LENGTH = 9;

    public function validate(string $siren): ValidationResult
    {
        $normalized = str_replace(' ', '', $siren);

        if (strlen($normalized) !== self::LENGTH) {
            return ValidationResult::failure(SirenFailureReason::INVALID_LENGTH);
        }

        /**
         * $normalized is already exactly LENGTH (9) characters, so the ^
         * and $ anchors can never change the match result.
         *
         * @infection-ignore-all
         */
        if (preg_match('/^\d{9}$/', $normalized) !== 1) {
            return ValidationResult::failure(SirenFailureReason::INVALID_FORMAT);
        }

        if (!Luhn::isValid($normalized)) {
            return ValidationResult::failure(SirenFailureReason::INVALID_CHECKSUM);
        }

        return ValidationResult::success();
    }
}
