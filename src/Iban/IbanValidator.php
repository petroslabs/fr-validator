<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Iban;

use PetrosLabs\FrValidator\Rib\RibValidator;
use PetrosLabs\FrValidator\Rib\RibValidatorInterface;
use PetrosLabs\FrValidator\ValidationResult;

/**
 * Validates a French IBAN: "FR" + 2 check digits + a 23-character BBAN
 * which, for France, is exactly a RIB (bank code, branch code, account
 * number, RIB key). Two independent checks both have to hold: the generic
 * ISO 7064 MOD 97-10 check digits, and the embedded RIB's own key — a
 * forged/mistyped account number can still produce "valid-looking" IBAN
 * check digits around it, since those only protect the IBAN as a whole,
 * not the BBAN's internal bank/branch/account consistency.
 *
 * Out of scope: IBANs from other countries. This package validates French
 * identifiers specifically, not the general IBAN standard.
 *
 * @see https://en.wikipedia.org/wiki/International_Bank_Account_Number
 */
final class IbanValidator implements IbanValidatorInterface
{
    private const LENGTH = 27;

    private const COUNTRY_CODE = 'FR';

    public function __construct(
        private readonly RibValidatorInterface $ribValidator = new RibValidator(),
    ) {
    }

    public function validate(string $iban): ValidationResult
    {
        $normalized = strtoupper(str_replace(' ', '', $iban));

        if (strlen($normalized) !== self::LENGTH) {
            return ValidationResult::failure(IbanFailureReason::INVALID_LENGTH);
        }

        $countryCode = substr($normalized, 0, 2);

        if ($countryCode !== self::COUNTRY_CODE) {
            return ValidationResult::failure(IbanFailureReason::INVALID_COUNTRY_CODE);
        }

        $ibanCheckDigits = substr($normalized, 2, 2);

        /**
         * $normalized is already exactly LENGTH (27) characters, so the ^
         * and $ anchors can never change the match result.
         *
         * @infection-ignore-all
         */
        if (preg_match('/^\d{2}$/', $ibanCheckDigits) !== 1) {
            return ValidationResult::failure(IbanFailureReason::INVALID_FORMAT);
        }

        $bban = substr($normalized, 4);

        if (!$this->ribValidator->validate($bban)->isValid()) {
            return ValidationResult::failure(IbanFailureReason::INVALID_RIB);
        }

        if ($this->mod97($normalized) !== 1) {
            return ValidationResult::failure(IbanFailureReason::INVALID_CHECKSUM);
        }

        return ValidationResult::success();
    }

    /**
     * ISO 7064 MOD 97-10: move the country code + check digits to the end,
     * replace letters with two-digit numbers (A=10 ... Z=35), and compute
     * the remainder of the resulting (potentially huge) number modulo 97.
     * Done digit-by-digit to avoid needing bcmath/gmp for a number far
     * beyond native int range.
     */
    private function mod97(string $iban): int
    {
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);

        $remainder = 0;

        foreach (str_split($rearranged) as $char) {
            $value = ctype_digit($char) ? $char : (string) (ord($char) - ord('A') + 10);

            foreach (str_split($value) as $digit) {
                $remainder = ($remainder * 10 + (int) $digit) % 97;
            }
        }

        return $remainder;
    }
}
