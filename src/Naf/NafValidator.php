<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Naf;

use PetrosLabs\FrValidator\ValidationResult;

/**
 * Validates a French NAF/APE activity code: 4 digits + 1 letter (displayed
 * with a dot, e.g. "62.01Z", stored without it as "6201Z"). Unlike this
 * package's other validators, there's no checksum — a NAF code is valid
 * only if it's one of the 732 sub-classes of the official INSEE
 * nomenclature (see NafCodes), a closed list rather than an algorithm.
 *
 * This only proves the code exists in the nomenclature, not that it's the
 * code actually assigned to a given company — that requires a lookup
 * against the INSEE Sirene database, out of scope here.
 */
final class NafValidator implements NafValidatorInterface
{
    private const LENGTH = 5;

    public function validate(string $naf): ValidationResult
    {
        $normalized = strtoupper(str_replace(['.', ' '], '', $naf));

        if (strlen($normalized) !== self::LENGTH) {
            return ValidationResult::failure(NafFailureReason::INVALID_LENGTH);
        }

        /**
         * $normalized is already exactly LENGTH (5) characters, so the ^
         * and $ anchors can never change the match result.
         *
         * @infection-ignore-all
         */
        if (preg_match('/^\d{4}[A-Z]$/', $normalized) !== 1) {
            return ValidationResult::failure(NafFailureReason::INVALID_FORMAT);
        }

        if (!isset(NafCodes::CODES[$normalized])) {
            return ValidationResult::failure(NafFailureReason::UNKNOWN_CODE);
        }

        return ValidationResult::success();
    }
}
