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
            /**
             * The (int) cast is required for PHPStan (PHP 8 throws on
             * non-numeric string arithmetic, which it can't rule out
             * statically) but is a runtime no-op: $char is always a single
             * numeric digit character here.
             *
             * @infection-ignore-all
             */
            $digit = (int) $char;

            if ($position % 2 === 1) {
                $digit *= 2;

                /**
                 * $digit was just doubled, so it is always even (0, 2, 4,
                 * ..., 18) — it can never equal exactly 9, making ">" and
                 * ">=" provably equivalent at this point.
                 *
                 * @infection-ignore-all
                 */
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            /**
             * Flipping every accumulation to subtraction would just negate
             * the final $sum, and "-x mod 10 == 0" iff "x mod 10 == 0" for
             * any integer — the final equality-to-zero check below is
             * sign-invariant, so this can't change the validation result.
             *
             * @infection-ignore-all
             */
            $sum += $digit;
        }

        return $sum % 10 === 0;
    }
}
