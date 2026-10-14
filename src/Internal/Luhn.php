<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Internal;

/**
 * Shared Luhn checksum, used by both SIREN (9 digits) and SIRET (14 digits)
 * validation — the two are independent checks over different-length digit
 * strings, not one derived from the other.
 *
 * Not part of the public API: no backward-compatibility promise.
 */
final class Luhn
{
    public static function isValid(string $digits): bool
    {
        $sum = 0;

        foreach (str_split(strrev($digits)) as $position => $char) {
            $digit = (int) $char;

            if ($position % 2 === 1) {
                $digit *= 2;

                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return $sum % 10 === 0;
    }
}
