<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Vat;

use PetrosLabs\FrValidator\Siren\SirenValidator;
use PetrosLabs\FrValidator\Siren\SirenValidatorInterface;
use PetrosLabs\FrValidator\ValidationResult;

/**
 * Validates a French intra-community VAT number: "FR" + a 2-digit key +
 * the 9-digit SIREN, 13 characters total. Like SIRET, this requires two
 * independent things to hold: the embedded SIREN must itself be
 * Luhn-valid, AND the key must match
 * `(12 + 3 * (SIREN mod 97)) mod 97` — a forged SIREN wrapped in a
 * "matching" key is still wrong if that SIREN was never legitimately
 * assigned.
 *
 * Scope (v1): only the standard, fully numeric key produced by this
 * formula. Some historical French VAT numbers are known to use a key
 * containing letters instead; that format could not be confirmed against
 * a reliable primary source, so it isn't supported here.
 */
final class VatValidator implements VatValidatorInterface
{
    private const LENGTH = 13;

    private const COUNTRY_CODE = 'FR';

    public function __construct(
        private readonly SirenValidatorInterface $sirenValidator = new SirenValidator(),
    ) {
    }

    public function validate(string $vat): ValidationResult
    {
        $normalized = strtoupper(str_replace(' ', '', $vat));

        if (strlen($normalized) !== self::LENGTH) {
            return ValidationResult::failure(VatFailureReason::INVALID_LENGTH);
        }

        $countryCode = substr($normalized, 0, 2);

        if ($countryCode !== self::COUNTRY_CODE) {
            return ValidationResult::failure(VatFailureReason::INVALID_COUNTRY_CODE);
        }

        $key = substr($normalized, 2, 2);

        /**
         * $normalized is already exactly LENGTH (13) characters, so the ^
         * and $ anchors can never change the match result.
         *
         * @infection-ignore-all
         */
        if (preg_match('/^\d{2}$/', $key) !== 1) {
            return ValidationResult::failure(VatFailureReason::INVALID_FORMAT);
        }

        $siren = substr($normalized, 4, 9);

        if (!$this->sirenValidator->validate($siren)->isValid()) {
            return ValidationResult::failure(VatFailureReason::INVALID_SIREN_CHECKSUM);
        }

        /**
         * The (int) cast is required for PHPStan (PHP 8 throws on
         * non-numeric string arithmetic, which it can't rule out
         * statically) but is a runtime no-op: $siren was just confirmed to
         * be 9 plain digits by the SIREN check above.
         *
         * @infection-ignore-all
         */
        $expectedKey = (12 + 3 * ((int) $siren % 97)) % 97;

        if ((int) $key !== $expectedKey) {
            return ValidationResult::failure(VatFailureReason::INVALID_CHECKSUM);
        }

        return ValidationResult::success();
    }
}
