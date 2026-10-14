<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Siret;

use PetrosLabs\FrValidator\Internal\Luhn;
use PetrosLabs\FrValidator\Siren\SirenValidator;
use PetrosLabs\FrValidator\Siren\SirenValidatorInterface;
use PetrosLabs\FrValidator\ValidationResult;

/**
 * Validates a French establishment identifier (SIRET): 9-digit SIREN +
 * 5-digit NIC, 14 digits total. Valid when the embedded SIREN independently
 * satisfies its own Luhn checksum AND the full 14 digits satisfy Luhn too —
 * these are two separate checks, not one derived from the other.
 *
 * La Poste (SIREN 356000000) is a documented exception: when its own Luhn
 * check fails, it is still valid if the sum of all 14 digits is a multiple
 * of 5 (this is a fallback, not a replacement — La Poste's headquarters
 * SIRET actually does satisfy standard Luhn).
 *
 * @see https://inseefrlab.github.io/validinsee/reference/validation_sirene.html
 */
final class SiretValidator implements SiretValidatorInterface
{
    private const LENGTH = 14;

    private const LA_POSTE_SIREN = '356000000';

    public function __construct(
        private readonly SirenValidatorInterface $sirenValidator = new SirenValidator(),
    ) {
    }

    public function validate(string $siret): ValidationResult
    {
        $normalized = str_replace(' ', '', $siret);

        if (strlen($normalized) !== self::LENGTH) {
            return ValidationResult::failure(SiretFailureReason::INVALID_LENGTH);
        }

        /**
         * $normalized is already exactly LENGTH (14) characters, so the ^
         * and $ anchors can never change the match result.
         *
         * @infection-ignore-all
         */
        if (preg_match('/^\d{14}$/', $normalized) !== 1) {
            return ValidationResult::failure(SiretFailureReason::INVALID_FORMAT);
        }

        $siren = substr($normalized, 0, 9);

        if (!$this->sirenValidator->validate($siren)->isValid()) {
            return ValidationResult::failure(SiretFailureReason::INVALID_SIREN_CHECKSUM);
        }

        if (Luhn::isValid($normalized)) {
            return ValidationResult::success();
        }

        if ($siren === self::LA_POSTE_SIREN && $this->digitSum($normalized) % 5 === 0) {
            return ValidationResult::success();
        }

        return ValidationResult::failure(SiretFailureReason::INVALID_CHECKSUM);
    }

    private function digitSum(string $digits): int
    {
        return array_sum(array_map('intval', str_split($digits)));
    }
}
